import Constants from 'expo-constants';
import * as Device from 'expo-device';
import * as Linking from 'expo-linking';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as LocalAuthentication from 'expo-local-authentication';
import * as Notifications from 'expo-notifications';
import { StatusBar } from 'expo-status-bar';
import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  AppState,
  AppStateStatus,
  ActivityIndicator,
  Alert,
  BackHandler,
  Modal,
  Platform,
  StyleSheet,
  Switch,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import WebView, { WebViewNavigation } from 'react-native-webview';

const WEBSITE_URL =
  (Constants.expoConfig?.extra?.websiteUrl as string | undefined) ?? 'https://tradewithkay.com';
const WEBSITE_HOSTS = new Set([new URL(WEBSITE_URL).hostname, `www.${new URL(WEBSITE_URL).hostname}`]);
const INTRO_KEY = 'kayxchange_native_intro_seen_v1';
const BIOMETRIC_KEY = 'kayxchange_biometric_unlock_v1';

const INTRO_PAGES = [
  {
    mark: '₦',
    eyebrow: 'YOUR MONEY, IN ONE PLACE',
    title: 'Move with confidence',
    body: 'Manage your naira and crypto activity from a secure, mobile-first KayXchange experience.',
  },
  {
    mark: '↗',
    eyebrow: 'TRADE ON YOUR TERMS',
    title: 'A clearer way to trade',
    body: 'Buy, sell, swap, deposit and withdraw with your account and transaction history close at hand.',
  },
  {
    mark: '⌁',
    eyebrow: 'STAY IN THE LOOP',
    title: 'Updates when they matter',
    body: 'Choose to receive trade and account alerts. You can enable biometric unlock on this device too.',
  },
];

function isTrustedWebsiteUrl(rawUrl: string): boolean {
  try {
    const url = new URL(rawUrl);
    return url.protocol === 'https:' && WEBSITE_HOSTS.has(url.hostname.toLowerCase());
  } catch {
    return false;
  }
}

function openExternalUrl(rawUrl: string) {
  try {
    const url = new URL(rawUrl);
    const allowedExternalProtocols = new Set(['https:', 'http:', 'mailto:', 'tel:']);
    if (!allowedExternalProtocols.has(url.protocol)) return;
    Linking.openURL(rawUrl).catch(() => Alert.alert('Unable to open link', 'Please try again or open the link in your browser.'));
  } catch {
    Alert.alert('Invalid link', 'This link could not be opened.');
  }
}

function normalizeNotificationUrl(value: unknown): string | null {
  if (typeof value !== 'string' || !value.trim()) return null;
  try {
    const url = new URL(value, WEBSITE_URL);
    return isTrustedWebsiteUrl(url.href) ? url.href : null;
  } catch {
    return null;
  }
}

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

async function registerForPushNotificationsAsync(): Promise<string | null> {
  if (!Device.isDevice) {
    return null;
  }

  const projectId =
    Constants.easConfig?.projectId ??
    (Constants.expoConfig?.extra?.eas as { projectId?: string } | undefined)?.projectId;

  if (!projectId || projectId === 'REPLACE_WITH_EAS_PROJECT_ID') {
    return null;
  }

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('default', {
      name: 'Default',
      importance: Notifications.AndroidImportance.MAX,
      vibrationPattern: [0, 250, 250, 250],
      lightColor: '#00c851',
    });
  }

  const existing = await Notifications.getPermissionsAsync();
  let finalStatus = existing.status;

  if (existing.status !== 'granted') {
    const requested = await Notifications.requestPermissionsAsync();
    finalStatus = requested.status;
  }

  if (finalStatus !== 'granted') {
    return null;
  }

  const token = await Notifications.getExpoPushTokenAsync({ projectId });
  return token.data;
}

function buildTokenInjectionScript(token: string | null) {
  return `
    (function () {
      var token = ${JSON.stringify(token)};
      var platform = ${JSON.stringify(Platform.OS)};
      var lastSentKey = 'kayxchange_expo_push_token_sent';

      window.KAYXCHANGE_NATIVE_APP = true;
      function isSignedIn() {
        return !!document.querySelector('form[action$="/logout"], a[href$="/logout"]');
      }
      if (window.ReactNativeWebView) {
        window.ReactNativeWebView.postMessage(isSignedIn() ? 'kayxchange:signed-in' : 'kayxchange:signed-out');
      }

      function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
      }

      function sendToken() {
        if (!token) return;
        if (!isSignedIn()) {
          if (localStorage.getItem(lastSentKey) === token) {
            fetch('/mobile/push-token', {
              method: 'DELETE', credentials: 'same-origin',
              headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrfToken()},
              body: JSON.stringify({token: token})
            }).catch(function () {});
            localStorage.removeItem(lastSentKey);
          }
          return;
        }
        if (localStorage.getItem(lastSentKey) === token) return;

        fetch('/mobile/push-token', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken()
          },
          body: JSON.stringify({
            token: token,
            platform: platform,
            device_name: navigator.userAgent
          })
        }).then(function (response) {
          if (response.ok) {
            localStorage.setItem(lastSentKey, token);
          }
        }).catch(function () {});
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sendToken);
      } else {
        sendToken();
      }

      true;
    })();
  `;
}

export default function App() {
  const webViewRef = useRef<WebView>(null);
  const appState = useRef<AppStateStatus>(AppState.currentState);
  const backgroundedAt = useRef<number | null>(null);
  const forceLoginStarted = useRef(false);
  const authInProgress = useRef(false);
  const [canGoBack, setCanGoBack] = useState(false);
  const [hasError, setHasError] = useState(false);
  const [pushToken, setPushToken] = useState<string | null>(null);
  const [introPage, setIntroPage] = useState<number | null>(null);
  const [entryPath, setEntryPath] = useState<string | null>(null);
  const [biometricEnabled, setBiometricEnabled] = useState(false);
  const [biometricReady, setBiometricReady] = useState(false);
  const [isLocked, setIsLocked] = useState(false);
  const [unlockError, setUnlockError] = useState('');
  const [securitySettingsVisible, setSecuritySettingsVisible] = useState(false);
  const [forceLoginAfterRecovery, setForceLoginAfterRecovery] = useState(false);
  const [loading, setLoading] = useState(true);
  const [hasLoadedOnce, setHasLoadedOnce] = useState(false);
  const [loadProgress, setLoadProgress] = useState(0);
  const pushSetupStarted = useRef(false);

  useEffect(() => {
    let mounted = true;
    Promise.all([AsyncStorage.getItem(INTRO_KEY), AsyncStorage.getItem(BIOMETRIC_KEY)])
      .then(([introSeen, biometricChoice]) => {
        if (!mounted) return;
        if (!introSeen) setIntroPage(0);
        const enabled = biometricChoice === 'enabled';
        setBiometricEnabled(enabled);
        setIsLocked(enabled);
        setBiometricReady(true);
      })
      .catch(() => {
        if (mounted) {
          setIntroPage(0);
          setBiometricReady(true);
        }
      });
    return () => { mounted = false; };
  }, []);

  const unlockWithBiometrics = useCallback(async () => {
    if (authInProgress.current) return false;
    authInProgress.current = true;
    setUnlockError('');
    try {
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: 'Unlock KayXchange',
        cancelLabel: 'Cancel',
        fallbackLabel: 'Use device passcode',
        disableDeviceFallback: false,
        biometricsSecurityLevel: 'weak',
        requireConfirmation: false,
      });
      if (result.success) {
        setIsLocked(false);
        return true;
      }
      const messages: Record<string, string> = {
        lockout: 'Biometrics are temporarily locked by your device. Wait a moment, then retry or use secure sign-in.',
        not_enrolled: 'No biometrics are enrolled on this device. Use secure sign-in to continue.',
        passcode_not_set: 'Set a device passcode to use biometric unlock, or continue with secure sign-in.',
        not_available: 'Biometric unlock is unavailable on this device right now. Use secure sign-in to continue.',
        user_cancel: '',
        system_cancel: 'The device cancelled the biometric check. Try again or use secure sign-in.',
        authentication_failed: 'Identity was not confirmed. Try again or use secure sign-in.',
      };
      setUnlockError(messages[result.error] ?? 'Biometric check did not complete. Try again or use secure sign-in.');
      return false;
    } catch {
      setUnlockError('Biometric unlock is unavailable right now. Try again or use secure sign-in.');
      return false;
    } finally {
      authInProgress.current = false;
    }
  }, []);

  useEffect(() => {
    if (!biometricReady || !biometricEnabled || !isLocked) return;
    unlockWithBiometrics();
  }, [biometricReady, biometricEnabled, isLocked, unlockWithBiometrics]);

  useEffect(() => {
    const subscription = AppState.addEventListener('change', (nextState) => {
      const previousState = appState.current;
      appState.current = nextState;
      if (nextState === 'background') {
        backgroundedAt.current = Date.now();
        return;
      }
      if (nextState === 'active') {
        const awayFor = backgroundedAt.current === null ? 0 : Date.now() - backgroundedAt.current;
        backgroundedAt.current = null;
        // Ignore short interruptions (permission sheets, app switcher flashes, notification shade).
        if (previousState === 'background' && awayFor >= 30_000 && biometricEnabled) {
          setUnlockError('');
          setIsLocked(true);
        }
      }
    });
    return () => subscription.remove();
  }, [biometricEnabled]);

  useEffect(() => {
    const subscription = Notifications.addNotificationResponseReceivedListener((response) => {
      const url = normalizeNotificationUrl(response.notification.request.content.data?.url);
      if (url) {
        webViewRef.current?.injectJavaScript(`window.location.href = ${JSON.stringify(url)}; true;`);
      }
    });

    return () => subscription.remove();
  }, []);

  useEffect(() => {
    const backHandler = BackHandler.addEventListener('hardwareBackPress', () => {
      if (canGoBack) {
        webViewRef.current?.goBack();
        return true;
      }
      return false;
    });

    return () => backHandler.remove();
  }, [canGoBack]);

  const injectedJavaScript = useMemo(() => buildTokenInjectionScript(pushToken), [pushToken]);

  useEffect(() => {
    if (pushToken) {
      webViewRef.current?.injectJavaScript(injectedJavaScript);
    }
  }, [injectedJavaScript, pushToken]);

  const handleNavigationStateChange = useCallback((navState: WebViewNavigation) => {
    setCanGoBack(navState.canGoBack);
  }, []);

  const handleShouldStartLoad = useCallback((request: { url: string }) => {
    if (isTrustedWebsiteUrl(request.url)) {
      return true;
    }

    if (request.url.startsWith('about:blank')) return false;
    openExternalUrl(request.url);
    return false;
  }, []);

  const handleOpenWindow = useCallback((event: { nativeEvent: { targetUrl: string } }) => {
    const target = event.nativeEvent.targetUrl;
    if (isTrustedWebsiteUrl(target)) {
      webViewRef.current?.injectJavaScript(`window.location.href = ${JSON.stringify(target)}; true;`);
    } else {
      openExternalUrl(target);
    }
  }, []);

  const handleWebViewMessage = useCallback((event: { nativeEvent: { data: string } }) => {
    if (event.nativeEvent.data !== 'kayxchange:signed-in' || pushToken || pushSetupStarted.current) return;
    pushSetupStarted.current = true;
    registerForPushNotificationsAsync()
      .then(setPushToken)
      .catch(() => setPushToken(null));
  }, [pushToken]);

  const finishIntro = useCallback(async (path: '/login' | '/register') => {
    try { await AsyncStorage.setItem(INTRO_KEY, 'seen'); } catch { /* keep onboarding usable if storage is unavailable */ }
    setEntryPath(path);
    setIntroPage(null);
  }, []);

  const enableBiometrics = useCallback(async () => {
    try {
      const hardware = await LocalAuthentication.hasHardwareAsync();
      const enrolled = hardware && await LocalAuthentication.isEnrolledAsync();
      if (!hardware || !enrolled) {
        Alert.alert('Biometrics unavailable', 'Set up Face ID, fingerprint or a device passcode in your phone settings first.');
        return;
      }
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: 'Confirm to enable KayXchange unlock',
        cancelLabel: 'Cancel',
        fallbackLabel: 'Use device passcode',
        disableDeviceFallback: false,
        biometricsSecurityLevel: 'weak',
        requireConfirmation: false,
      });
      if (!result.success) return;
      await AsyncStorage.setItem(BIOMETRIC_KEY, 'enabled');
      setBiometricEnabled(true);
      Alert.alert('Biometric unlock enabled', 'KayXchange will ask for biometrics or your device passcode when you reopen the app.');
    } catch {
      Alert.alert('Could not enable unlock', 'Please try again from this device.');
    }
  }, []);

  const disableBiometrics = useCallback(async () => {
    try {
      await AsyncStorage.setItem(BIOMETRIC_KEY, 'disabled');
      setBiometricEnabled(false);
      setIsLocked(false);
      setUnlockError('');
      setSecuritySettingsVisible(false);
    } catch {
      Alert.alert('Could not update security setting', 'Please try again.');
    }
  }, []);

  const recoverToWebsiteSignIn = useCallback(() => {
    Alert.alert(
      'Continue to secure sign-in?',
      'This turns off biometric unlock on this device and signs out the saved website session. You can then sign in again with your KayXchange account credentials.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Continue', style: 'destructive', onPress: async () => {
          try {
            await AsyncStorage.setItem(BIOMETRIC_KEY, 'disabled');
            setBiometricEnabled(false);
            setEntryPath('/login');
            setForceLoginAfterRecovery(true);
            forceLoginStarted.current = false;
            setIsLocked(false);
            setUnlockError('');
          } catch {
            setUnlockError('Could not turn off biometric unlock. Retry or restart the app.');
          }
        } },
      ],
    );
  }, []);

  const completeRecoveryLogout = useCallback(() => {
    if (!forceLoginAfterRecovery || forceLoginStarted.current) return;
    forceLoginStarted.current = true;
    webViewRef.current?.injectJavaScript(`
      (function(){
        var form=document.querySelector('form[action$="/logout"]');
        if(form){form.submit();return;}
        var csrf=document.querySelector('meta[name="csrf-token"]');
        if(csrf){fetch('/logout',{method:'POST',credentials:'same-origin',headers:{'X-CSRF-TOKEN':csrf.content,'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}}).then(function(){location.replace('/login')}).catch(function(){location.replace('/login')});}
        else{location.replace('/login')}
      })();true;
    `);
    setForceLoginAfterRecovery(false);
  }, [forceLoginAfterRecovery]);

  const startSignup = useCallback(() => finishIntro('/register'), [finishIntro]);
  const startLogin = useCallback(() => finishIntro('/login'), [finishIntro]);

  const retry = () => {
    setHasError(false);
    setLoading(true);
    setHasLoadedOnce(false);
    setLoadProgress(0);
    webViewRef.current?.reload();
  };

  if (!biometricReady) {
    return (
      <SafeAreaView style={styles.centered}>
        <ActivityIndicator color="#25d366" />
        <StatusBar style="light" />
      </SafeAreaView>
    );
  }

  if (isLocked) {
    return (
      <SafeAreaView style={styles.centered}>
        <View style={styles.brandMark}><Text style={styles.brandMarkText}>K</Text></View>
        <Text style={styles.eyebrow}>SECURE DEVICE UNLOCK</Text>
        <Text style={styles.title}>Welcome back</Text>
        <Text style={styles.message}>Unlock KayXchange with biometrics or your device passcode.</Text>
        {!!unlockError && <Text style={styles.errorMessage}>{unlockError}</Text>}
        <TouchableOpacity accessibilityRole="button" style={styles.button} onPress={unlockWithBiometrics}>
          <Text style={styles.buttonText}>Unlock KayXchange</Text>
        </TouchableOpacity>
        <TouchableOpacity accessibilityRole="button" style={styles.recoveryButton} onPress={recoverToWebsiteSignIn}>
          <Text style={styles.recoveryButtonText}>Biometrics not working? Continue to sign in</Text>
        </TouchableOpacity>
        <Text style={styles.recoveryHint}>This safely disables device unlock and asks you to sign in again.</Text>
        <StatusBar style="light" />
      </SafeAreaView>
    );
  }

  if (introPage !== null) {
    const page = INTRO_PAGES[introPage];
    return (
      <SafeAreaView style={styles.introContainer}>
        <View style={styles.introTop}>
          <View style={styles.introBrand}><View style={styles.brandMarkSmall}><Text style={styles.brandMarkTextSmall}>K</Text></View><Text style={styles.introBrandName}>KayXchange</Text></View>
          <TouchableOpacity accessibilityRole="button" onPress={startLogin}><Text style={styles.skipText}>Skip</Text></TouchableOpacity>
        </View>
        <View style={styles.introContent}>
          <View style={styles.heroGlow}><Text style={styles.heroMark}>{page.mark}</Text><View style={styles.heroDot} /></View>
          <Text style={styles.introEyebrow}>{page.eyebrow}</Text>
          <Text style={styles.introTitle}>{page.title}</Text>
          <Text style={styles.introBody}>{page.body}</Text>
          <View style={styles.pagination}>{INTRO_PAGES.map((_, index) => <View key={index} style={[styles.pageDot, index === introPage && styles.pageDotActive]} />)}</View>
        </View>
        <View style={styles.introActions}>
          {introPage < INTRO_PAGES.length - 1 ? (
            <TouchableOpacity accessibilityRole="button" style={styles.button} onPress={() => setIntroPage(introPage + 1)}>
              <Text style={styles.buttonText}>Continue</Text>
            </TouchableOpacity>
          ) : (
            <>
              <TouchableOpacity accessibilityRole="button" style={styles.button} onPress={startSignup}>
                <Text style={styles.buttonText}>Create your account</Text>
              </TouchableOpacity>
              <TouchableOpacity accessibilityRole="button" style={styles.loginButton} onPress={startLogin}>
                <Text style={styles.loginButtonText}>I already have an account · Log in</Text>
              </TouchableOpacity>
              <TouchableOpacity accessibilityRole="button" style={styles.securityButton} onPress={enableBiometrics}>
                <Text style={styles.securityButtonText}>{biometricEnabled ? '✓ Biometric unlock is on' : 'Enable Face ID / fingerprint'}</Text>
              </TouchableOpacity>
              <Text style={styles.permissionNote}>Trade alerts can be enabled after you sign in.</Text>
            </>
          )}
        </View>
        <StatusBar style="light" />
      </SafeAreaView>
    );
  }

  if (hasError) {
    return (
      <SafeAreaView style={styles.centered}>
        <View style={styles.brandMark}><Text style={styles.brandMarkText}>K</Text></View>
        <Text style={styles.eyebrow}>TRADE WITH KAY</Text>
        <Text style={styles.title}>You’re offline?</Text>
        <Text style={styles.message}>We couldn’t reach the live KayXchange website. Check your connection and try again.</Text>
        <TouchableOpacity accessibilityRole="button" style={styles.button} onPress={retry}>
          <Text style={styles.buttonText}>Try again</Text>
        </TouchableOpacity>
        <TouchableOpacity accessibilityRole="link" style={styles.browserLink} onPress={() => openExternalUrl(WEBSITE_URL)}>
          <Text style={styles.browserLinkText}>Open in browser</Text>
        </TouchableOpacity>
        <StatusBar style="light" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top', 'left', 'right', 'bottom']}>
      {loading && !hasLoadedOnce && <View style={styles.loadingOverlay} pointerEvents="none">
        <View style={styles.brandMark}><Text style={styles.brandMarkText}>K</Text></View>
        <Text style={styles.loadingTitle}>KayXchange</Text>
        <ActivityIndicator color="#25d366" size="small" style={styles.spinner} />
        <Text style={styles.loadingMessage}>Connecting securely…</Text>
      </View>}
      {loading && hasLoadedOnce && <View style={styles.progressTrack} pointerEvents="none"><View style={[styles.progressBar, { width: `${Math.max(8, loadProgress * 100)}%` }]} /></View>}
      <WebView
        ref={webViewRef}
        source={{ uri: entryPath ? new URL(entryPath, WEBSITE_URL).href : WEBSITE_URL }}
        originWhitelist={['https://*']}
        sharedCookiesEnabled
        thirdPartyCookiesEnabled
        javaScriptEnabled
        domStorageEnabled
        cacheEnabled
        setSupportMultipleWindows={false}
        allowsBackForwardNavigationGestures
        injectedJavaScript={injectedJavaScript}
        onNavigationStateChange={handleNavigationStateChange}
        onShouldStartLoadWithRequest={handleShouldStartLoad}
        onOpenWindow={handleOpenWindow}
        onMessage={handleWebViewMessage}
        onLoadStart={() => { setLoading(true); setLoadProgress(0); }}
        onLoadProgress={({ nativeEvent }) => setLoadProgress(nativeEvent.progress)}
        onLoadEnd={() => { setLoading(false); setHasLoadedOnce(true); setLoadProgress(1); completeRecoveryLogout(); }}
        onError={() => { setLoading(false); setHasError(true); }}
        onHttpError={(event) => {
          if (event.nativeEvent.statusCode >= 500 && (event.nativeEvent as typeof event.nativeEvent & { isForMainFrame?: boolean }).isForMainFrame !== false) {
            setLoading(false);
            setHasError(true);
          }
        }}
        onRenderProcessGone={() => {
          setLoading(false);
          setHasError(true);
        }}
        startInLoadingState={false}
        style={styles.webView}
      />
      <View style={styles.securityFabWrap} pointerEvents="box-none">
        <TouchableOpacity accessibilityRole="button" accessibilityLabel="App security settings" style={styles.securityFab} onPress={() => setSecuritySettingsVisible(true)}>
          <Text style={styles.securityFabIcon}>⚙</Text>
        </TouchableOpacity>
      </View>
      <Modal visible={securitySettingsVisible} transparent animationType="fade" onRequestClose={() => setSecuritySettingsVisible(false)}>
        <View style={styles.modalBackdrop}>
          <View style={styles.securityModal}>
            <View style={styles.modalIcon}><Text style={styles.modalIconText}>⌑</Text></View>
            <Text style={styles.modalTitle}>App security</Text>
            <Text style={styles.modalBody}>Choose whether KayXchange asks for your device biometrics when you return to the app.</Text>
            <View style={styles.securityOption}>
              <View style={styles.securityOptionText}><Text style={styles.securityOptionTitle}>Biometric unlock</Text><Text style={styles.securityOptionSub}>{biometricEnabled ? 'Enabled on this device' : 'Disabled on this device'}</Text></View>
              <Switch
                value={biometricEnabled}
                onValueChange={(enabled) => { if (enabled) enableBiometrics(); else disableBiometrics(); }}
                trackColor={{ false: '#39464d', true: '#168b50' }}
                thumbColor={biometricEnabled ? '#d8ffe4' : '#d1d5db'}
                accessibilityLabel="Toggle biometric unlock"
              />
            </View>
            <Text style={styles.modalFootnote}>Turning this off affects only app unlock on this phone. Your KayXchange account password and transaction PIN remain unchanged.</Text>
            <TouchableOpacity style={styles.modalDone} onPress={() => setSecuritySettingsVisible(false)}><Text style={styles.modalDoneText}>Done</Text></TouchableOpacity>
          </View>
        </View>
      </Modal>
      <StatusBar style="light" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0f1419',
  },
  webView: {
    flex: 1,
    backgroundColor: '#0b1118',
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    zIndex: 2,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#0f1419',
  },
  progressTrack: {
    position: 'absolute',
    left: 0,
    right: 0,
    top: 0,
    zIndex: 4,
    height: 3,
    backgroundColor: 'rgba(255,255,255,.08)',
  },
  progressBar: {
    height: 3,
    backgroundColor: '#25d366',
  },
  brandMark: {
    width: 58,
    height: 58,
    borderRadius: 19,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#25d366',
    marginBottom: 15,
  },
  brandMarkText: {
    color: '#06130a',
    fontSize: 31,
    fontWeight: '900',
  },
  eyebrow: {
    color: '#86efac',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 2,
    marginBottom: 8,
  },
  loadingTitle: {
    color: '#fff',
    fontSize: 21,
    fontWeight: '800',
  },
  spinner: {
    marginTop: 25,
  },
  loadingMessage: {
    color: '#91a0af',
    fontSize: 13,
    marginTop: 12,
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    backgroundColor: '#0f1419',
    justifyContent: 'center',
    padding: 24,
  },
  title: {
    color: '#ffffff',
    fontSize: 24,
    fontWeight: '700',
    marginBottom: 10,
  },
  browserLink: {
    minHeight: 42,
    justifyContent: 'center',
    paddingHorizontal: 16,
    marginTop: 8,
  },
  browserLinkText: {
    color: '#9ca3af',
    fontSize: 14,
    fontWeight: '600',
  },
  message: {
    color: '#c9d1d9',
    fontSize: 15,
    lineHeight: 22,
    marginBottom: 22,
    textAlign: 'center',
  },
  errorMessage: {
    color: '#ff9c9c',
    backgroundColor: 'rgba(239,68,68,.1)',
    borderColor: 'rgba(239,68,68,.22)',
    borderWidth: 1,
    borderRadius: 12,
    padding: 12,
    fontSize: 13,
    lineHeight: 19,
    marginBottom: 16,
    textAlign: 'center',
  },
  recoveryButton: {
    minHeight: 46,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 12,
    marginTop: 8,
  },
  recoveryButtonText: {
    color: '#8fe6a9',
    fontSize: 13,
    fontWeight: '700',
    textAlign: 'center',
  },
  recoveryHint: {
    color: '#7f8b96',
    fontSize: 11,
    lineHeight: 16,
    textAlign: 'center',
    marginTop: 3,
  },
  securityFabWrap: {
    ...StyleSheet.absoluteFillObject,
    zIndex: 8,
  },
  securityFab: {
    position: 'absolute',
    right: 14,
    bottom: 22,
    width: 46,
    height: 46,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 23,
    borderWidth: 1,
    borderColor: 'rgba(116,238,158,.45)',
    backgroundColor: 'rgba(10,22,15,.94)',
    elevation: 8,
  },
  securityFabIcon: { color: '#a6f2ba', fontSize: 22, fontWeight: '700' },
  modalBackdrop: {
    flex: 1,
    justifyContent: 'center',
    padding: 22,
    backgroundColor: 'rgba(0,0,0,.72)',
  },
  securityModal: {
    width: '100%',
    maxWidth: 460,
    alignSelf: 'center',
    padding: 22,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: 'rgba(139,157,170,.25)',
    backgroundColor: '#111a22',
  },
  modalIcon: {
    width: 46,
    height: 46,
    borderRadius: 15,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(37,211,102,.14)',
    marginBottom: 14,
  },
  modalIconText: { color: '#76ed9f', fontSize: 23, fontWeight: '800' },
  modalTitle: { color: '#f4f8f6', fontSize: 20, fontWeight: '800', marginBottom: 7 },
  modalBody: { color: '#a6b4be', fontSize: 13, lineHeight: 20 },
  securityOption: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 16,
    marginTop: 20,
    padding: 14,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(148,163,184,.16)',
    backgroundColor: 'rgba(255,255,255,.035)',
  },
  securityOptionText: { flex: 1 },
  securityOptionTitle: { color: '#eaf2ee', fontSize: 14, fontWeight: '700' },
  securityOptionSub: { color: '#8d9da7', fontSize: 12, marginTop: 4 },
  modalFootnote: { color: '#7f8d97', fontSize: 11, lineHeight: 17, marginTop: 14 },
  modalDone: {
    minHeight: 46,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 19,
    borderRadius: 12,
    backgroundColor: '#19bd55',
  },
  modalDoneText: { color: '#07140b', fontSize: 14, fontWeight: '800' },
  button: {
    alignItems: 'center',
    backgroundColor: '#00c851',
    borderRadius: 8,
    minHeight: 44,
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  buttonText: {
    color: '#071108',
    fontSize: 15,
    fontWeight: '700',
  },
  introContainer: {
    flex: 1,
    backgroundColor: '#0b1118',
    paddingHorizontal: 25,
    paddingTop: 12,
    paddingBottom: 18,
  },
  introTop: {
    height: 56,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  introBrand: { flexDirection: 'row', alignItems: 'center' },
  brandMarkSmall: {
    width: 34,
    height: 34,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#25d366',
  },
  brandMarkTextSmall: { color: '#06130a', fontSize: 19, fontWeight: '900' },
  introBrandName: { color: '#f3f7fa', fontSize: 15, fontWeight: '800', marginLeft: 10 },
  skipText: { color: '#94a3b8', fontSize: 14, fontWeight: '600', padding: 8 },
  introContent: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 8 },
  heroGlow: {
    width: 176,
    height: 176,
    borderRadius: 56,
    backgroundColor: '#10251b',
    borderWidth: 1,
    borderColor: 'rgba(37,211,102,.26)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 38,
  },
  heroMark: { color: '#55e78b', fontSize: 76, fontWeight: '800' },
  heroDot: { position: 'absolute', right: 29, top: 31, width: 15, height: 15, borderRadius: 8, backgroundColor: '#25d366' },
  introEyebrow: { color: '#63e58e', letterSpacing: 2.1, fontSize: 10, fontWeight: '800', marginBottom: 12, textAlign: 'center' },
  introTitle: { color: '#f7fafc', fontSize: 31, fontWeight: '800', lineHeight: 38, textAlign: 'center', marginBottom: 14 },
  introBody: { color: '#a7b2bf', fontSize: 15, lineHeight: 24, textAlign: 'center', maxWidth: 340 },
  pagination: { flexDirection: 'row', alignItems: 'center', marginTop: 34 },
  pageDot: { width: 6, height: 6, borderRadius: 4, backgroundColor: '#33404d', marginHorizontal: 4 },
  pageDotActive: { width: 23, backgroundColor: '#25d366' },
  introActions: { paddingBottom: 10 },
  loginButton: { minHeight: 48, alignItems: 'center', justifyContent: 'center', marginTop: 8 },
  loginButtonText: { color: '#d2d9df', fontWeight: '700', fontSize: 14 },
  securityButton: { minHeight: 42, alignItems: 'center', justifyContent: 'center', marginTop: 2 },
  securityButtonText: { color: '#83dfa3', fontSize: 13, fontWeight: '700' },
  permissionNote: { color: '#748191', fontSize: 11, textAlign: 'center', marginTop: 3 },
});
