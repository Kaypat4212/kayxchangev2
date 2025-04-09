import http.client
import json

conn = http.client.HTTPSConnection("wgkdrr.api.infobip.com")
payload = json.dumps({
    "messages": [
        {
            "destinations": [{"to":"+2348084076649"}],
            "from": "447491163443",
            "text": "Congratulations on sending your first message. Go ahead and check the delivery report in the next step."
        }
    ]
})
headers = {
    'Authorization': 'App 22f66da7a09fc0b394a849c5e048984b-1b9c2f37-6586-4482-8d77-ee85d65de9ed',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}
conn.request("POST", "/sms/2/text/advanced", payload, headers)
res = conn.getresponse()
data = res.read()
print(data.decode("utf-8"))