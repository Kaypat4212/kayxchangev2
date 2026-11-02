const POLL_INTERVAL_MS = 30000;

function notify(title, body) {
  if (!('Notification' in window)) return;

  if (Notification.permission === 'granted') {
    new Notification(title, { body });
    return;
  }

  if (Notification.permission !== 'denied') {
    Notification.requestPermission().then((permission) => {
      if (permission === 'granted') {
        new Notification(title, { body });
      }
    });
  }
}

window.addEventListener('DOMContentLoaded', () => {
  let lastUnreadCount = 0;

  async function pollNotifications() {
    try {
      const response = await fetch('/notifications/api?limit=1', {
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) return;

      const data = await response.json();
      const unreadCount = Number(data.unread_count || 0);
      const latest = Array.isArray(data.notifications) ? data.notifications[0] : null;

      if (unreadCount > lastUnreadCount && latest) {
        notify(latest.title || 'Trade With Kay', latest.message || 'You have a new notification.');
      }

      lastUnreadCount = unreadCount;
    } catch (_) {}
  }

  pollNotifications();
  setInterval(pollNotifications, POLL_INTERVAL_MS);
});
