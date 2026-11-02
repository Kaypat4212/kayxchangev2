import Constants from 'expo-constants';
import * as Device from 'expo-device';
import * as Linking from 'expo-linking';
import * as Notifications from 'expo-notifications';
import { StatusBar } from 'expo-status-bar';
import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  BackHandler,
  Platform,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import WebView, { WebViewNavigation } from 'react-native-webview';

const WEBSITE_URL =
  (Constants.expoConfig?.extra?.websiteUrl as string | undefined) ?? 'https://tradewithkay.com';

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

  const projectId =
    Constants.easConfig?.projectId ??
    (Constants.expoConfig?.extra?.eas as { projectId?: string } | undefined)?.projectId;

  if (!projectId || projectId === 'REPLACE_WITH_EAS_PROJECT_ID') {
    return null;
  }

  const token = await Notifications.getExpoPushTokenAsync({ projectId });
  return token.data;
}

function buildTokenInjectionScript(token: string | null) {
  if (!token) {
    return 'true;';
  }

  return `
    (function () {
      var token = ${JSON.stringify(token)};
      var platform = ${JSON.stringify(Platform.OS)};
      var lastSentKey = 'kayxchange_expo_push_token_sent';

      function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
      }

      function sendToken() {
        if (!token || localStorage.getItem(lastSentKey) === token) return;

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

      window.KAYXCHANGE_NATIVE_APP = true;
      true;
    })();
  `;
}

export default function App() {
  const webViewRef = useRef<WebView>(null);
  const [canGoBack, setCanGoBack] = useState(false);
  const [hasError, setHasError] = useState(false);
  const [pushToken, setPushToken] = useState<string | null>(null);

  useEffect(() => {
    registerForPushNotificationsAsync()
      .then(setPushToken)
      .catch(() => setPushToken(null));

    const subscription = Notifications.addNotificationResponseReceivedListener((response) => {
      const url = response.notification.request.content.data?.url;
      if (typeof url === 'string' && url.startsWith(WEBSITE_URL)) {
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

  const handleNavigationStateChange = useCallback((navState: WebViewNavigation) => {
    setCanGoBack(navState.canGoBack);
  }, []);

  const handleShouldStartLoad = useCallback((request: { url: string }) => {
    const isTradeWithKay = request.url.startsWith(WEBSITE_URL);
    const isHttp = request.url.startsWith('http://') || request.url.startsWith('https://');

    if (!isTradeWithKay && isHttp) {
      Linking.openURL(request.url).catch(() => Alert.alert('Unable to open link'));
      return false;
    }

    return true;
  }, []);

  if (hasError) {
    return (
      <SafeAreaView style={styles.centered}>
        <Text style={styles.title}>Trade With Kay</Text>
        <Text style={styles.message}>We could not load the website. Check your connection and try again.</Text>
        <TouchableOpacity style={styles.button} onPress={() => setHasError(false)}>
          <Text style={styles.buttonText}>Retry</Text>
        </TouchableOpacity>
        <StatusBar style="light" />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container} edges={['top', 'left', 'right']}>
      <WebView
        ref={webViewRef}
        source={{ uri: WEBSITE_URL }}
        sharedCookiesEnabled
        thirdPartyCookiesEnabled
        javaScriptEnabled
        domStorageEnabled
        injectedJavaScript={injectedJavaScript}
        onNavigationStateChange={handleNavigationStateChange}
        onShouldStartLoadWithRequest={handleShouldStartLoad}
        onError={() => setHasError(true)}
        onHttpError={(event) => {
          if (event.nativeEvent.statusCode >= 500) {
            setHasError(true);
          }
        }}
        startInLoadingState
        renderLoading={() => (
          <View style={styles.loader}>
            <ActivityIndicator color="#00c851" size="large" />
          </View>
        )}
      />
      <StatusBar style="light" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#0f1419',
  },
  loader: {
    ...StyleSheet.absoluteFillObject,
    alignItems: 'center',
    backgroundColor: '#0f1419',
    justifyContent: 'center',
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
  message: {
    color: '#c9d1d9',
    fontSize: 15,
    lineHeight: 22,
    marginBottom: 22,
    textAlign: 'center',
  },
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
});
