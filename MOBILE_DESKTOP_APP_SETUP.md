# Trade With Kay Native App Setup

This repo now includes:

- `mobile-app/`: Expo app that opens the live `https://tradewithkay.com` website in a WebView. First launch has a native welcome flow with direct paths to the existing website login and registration pages. Optional Face ID/fingerprint unlock protects app reopening on the current device; it does not replace website sign-in. Push permission is requested after sign-in, and notification taps open trusted KayXchange pages.
- `desktop-app/`: Electron wrapper for Windows `.exe` builds.
- Laravel push-token backend: `/mobile/push-token`, `MobilePushToken`, and `ExpoPushNotificationService`.

## Backend

Run the migration:

```bash
php artisan migrate
```

Send a push notification from Laravel code:

```php
app(\App\Services\ExpoPushNotificationService::class)->sendToUser(
    $user,
    'Trade update',
    'Your trade status has changed.',
    ['url' => url('/notifications')]
);
```

Broadcast to all registered app users:

```php
app(\App\Services\ExpoPushNotificationService::class)->sendBroadcast(
    'KayXchange',
    'New update available.',
    ['url' => url('/dashboard')]
);
```

## Mobile App

Install dependencies:

```bash
cd mobile-app
npm install
```

The app identifiers are set to `com.tradewithkay.app` for iOS and Android. This app is linked to the Expo project `@patrickezike66/tradewithkay` (`fb372d03-8920-4585-b680-7718cf3d25a2`). To relink it later, run:

```bash
npx eas init
```

The EAS project ID is stored in `mobile-app/app.json`. Push-token registration uses this linked project ID.

The first-run intro routes users to the existing `/login` and `/register` website pages. Biometric unlock is optional and uses the device's enrolled biometrics or passcode. Push notifications require the Laravel push-token endpoint and valid Expo/FCM/APNs credentials; push permission is requested only after sign-in.

Preview the live-site shell with Expo Go only if you install an Expo Go version compatible with the app's SDK. This app currently uses SDK 52, so the current Expo Go app may not accept it:

```bash
npm start
```

Build installable files:

```bash
npm run build:android:apk
npm run build:android:store
npm run build:ios
```

For a reliable preview, use an EAS development build. Remote push notifications and store-ready builds also use EAS; remote delivery requires Apple and Google push credentials configured there.

### Release checklist

1. Upgrade the app from its current Expo SDK 52 baseline to the current stable Expo SDK before preparing store submissions; follow Expo's SDK upgrade steps and re-run `npx expo install --fix` after each SDK step.
2. Build and test on real iOS and Android devices. Check sign-in, KYC uploads, camera/file pickers, payments, crypto deposits, browser returns, logout, and notification permissions.
3. Configure Apple signing and push credentials in EAS, then build iOS. iOS release builds require macOS/EAS and an Apple Developer account.
4. Configure Android signing and FCM push credentials in EAS, then build an APK for internal testing and an AAB for Google Play.
5. Prepare the store listings, privacy disclosures, support contact, and financial-service review materials before submitting.

The website remains the source of content and account data. Changes to the live website appear in the app without publishing a new app build, while native-shell changes (icons, permissions, app identifiers, and notification setup) require a new build.

## Windows App

Install dependencies:

```bash
cd desktop-app
npm install
```

Run locally:

```bash
npm start
```

Build `.exe` installers:

```bash
npm run dist
```

The desktop wrapper can show notifications while the app is open by polling the website notifications endpoint. Mobile push notifications can arrive even when the mobile app is backgrounded, subject to iOS/Android delivery rules.
