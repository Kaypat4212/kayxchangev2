# Trade With Kay Native App Setup

This repo now includes:

- `mobile-app/`: Expo app that opens `https://tradewithkay.com` in a WebView and registers Expo push tokens for logged-in users.
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

Create/link an Expo Application Services project:

```bash
npx eas init
```

Replace `REPLACE_WITH_EAS_PROJECT_ID` in `mobile-app/app.json` with the generated project ID.

Preview:

```bash
npm start
```

Build installable files:

```bash
npm run build:android:apk
npm run build:android:store
npm run build:ios
```

Expo Go is useful for previewing the WebView shell. For dependable remote push notifications and store-ready builds, use EAS development or production builds.

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
