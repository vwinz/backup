import sys
import vonage

def send_verification_code(phone_number, code):
    client = vonage.Client(key="01138807", secret="hyLfUlsxqs8ldB0B")
    sms = vonage.Sms(client)
    responseData = sms.send_message(
        {
            "from": "Vonage APIs",
            "to": phone_number,
            "text": f"tanginamo francine ito ang verification code: {code}",
        }
    )
    if responseData["messages"][0]["status"] == "0":
        print("Message sent successfully.")
    else:
        print("Message failed with error: " + responseData["messages"][0]["error-text"])

if __name__ == "__main__":
    phone_number = sys.argv[1]
    code = sys.argv[2]
    send_verification_code(phone_number, code)

