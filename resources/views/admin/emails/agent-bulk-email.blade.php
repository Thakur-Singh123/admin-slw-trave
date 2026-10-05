<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectText }}</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        background: #f4f6f8;
        font-family: Arial, Helvetica, sans-serif;
        color: #172033;
    }
    table {
        border-spacing: 0;
        border-collapse: collapse;
    }
    .email-wrapper {
        width: 100%;
        background: #f4f6f8;
        padding: 30px 15px;
    }
    .email-container {
        width: 600px;
        max-width: 100%;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
    }
    .email-header {
        background: #101827;
        padding: 25px;
        text-align: center;
        border-bottom: 5px solid #16a34a;
    }
    .email-logo {
        max-width: 190px;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .email-content {
        padding: 35px 30px;
    }
    .email-title {
        margin: 0 0 22px;
        font-size: 28px;
        line-height: 1.3;
        font-weight: 700;
        color: #172033;
        text-align: center;
    }
    .hello-text {
        margin: 0 0 20px;
        font-size: 16px;
        line-height: 1.7;
        color: #555555;
    }
    .message-box {
        font-size: 15px;
        line-height: 1.8;
        color: #555555;
    }
    .agent-box {
        margin-top: 30px;
        padding: 18px 20px;
        background: #f8f9fb;
        border-radius: 8px;
        border: 1px solid #eeeeee;
    }
    .agent-label {
        margin: 0 0 5px;
        font-size: 12px;
        color: #888888;
    }
    .agent-value {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #172033;
    }
    .regards {
        margin: 30px 0 0;
        font-size: 15px;
        line-height: 1.7;
        color: #555555;
    }
    .email-footer {
        background: #101827;
        padding: 22px 25px;
        text-align: center;
    }
    .footer-text {
        margin: 0;
        color: #ffffff;
        font-size: 13px;
        line-height: 1.5;
    }
    .footer-small {
        margin: 7px 0 0;
        color: #bfc5d2;
        font-size: 12px;
        line-height: 1.5;
    }
    @media only screen and (max-width: 600px) {
        .email-content {
            padding: 28px 20px;
        }

        .email-title {
            font-size: 24px;
        }
    }
    </style>
  </head>
  <body>
    <table width="100%" cellpadding="0" cellspacing="0" class="email-wrapper">
      <tr>
        <td align="center">
          <table width="600" cellpadding="0" cellspacing="0" class="email-container">
            <tr>
              <td class="email-header"><img src="{{ $message->embed(public_path('assets/images/logo.png')) }}" alt="Sun Leisure World" class="email-logo"></td>
            </tr>
            <tr>
              <td class="email-content">
                <h1 class="email-title">{{ $subjectText }}</h1>
                <p class="hello-text">Hello <strong>{{ $agent->name ?? 'Agent' }}</strong>,</p>
                <div class="message-box">{!! nl2br(e($body)) !!}</div>
                <div class="agent-box">
                  <p class="agent-label">Agent ID</p>
                  <p class="agent-value">AGT-{{ $agent->add_agent_id }}</p>
                  <br>
                  <p class="agent-label">Company</p>
                  <p class="agent-value">{{ $agent->name ?? 'N/A' }}</p>
                </div>
                <p class="regards">Regards,<br> <strong>{{ $senderName }}</strong> <br> Sun Leisure World</p>
              </td>
            </tr>
            <tr>
              <td class="email-footer">
                <p class="footer-text">© {{ date('Y') }} Sun Leisure World. All rights reserved.</p>
                <p class="footer-small">This email was sent to you as a registered B2B agent.</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>