import http.client
import json
import os

# Load credentials from environment variables — NEVER hardcode API keys
INFOBIP_BASE_URL = os.environ.get('INFOBIP_BASE_URL', '')  # e.g. wgkdrr.api.infobip.com
INFOBIP_API_KEY  = os.environ.get('INFOBIP_API_KEY', '')
SMS_FROM         = os.environ.get('INFOBIP_SMS_FROM', '')
SMS_TO           = os.environ.get('INFOBIP_SMS_TO', '')

if not all([INFOBIP_BASE_URL, INFOBIP_API_KEY, SMS_FROM, SMS_TO]):
    raise EnvironmentError('Set INFOBIP_BASE_URL, INFOBIP_API_KEY, INFOBIP_SMS_FROM, INFOBIP_SMS_TO env vars')

conn = http.client.HTTPSConnection(INFOBIP_BASE_URL)
payload = json.dumps({
    "messages": [
        {
            "destinations": [{"to": SMS_TO}],
            "from": SMS_FROM,
            "text": "Test message from KayXchange."
        }
    ]
})
headers = {
    'Authorization': f'App {INFOBIP_API_KEY}',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}
conn.request("POST", "/sms/2/text/advanced", payload, headers)
res = conn.getresponse()
data = res.read()
print(data.decode("utf-8"))