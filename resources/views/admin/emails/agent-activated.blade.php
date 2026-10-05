<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Account Activated</title>
</head>
<body style="
    margin:0;
    padding:0;
    background:#f5f7fa;
    font-family:Arial, Helvetica, sans-serif;
">
<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background:#f5f7fa;
        padding:35px 15px;
    "
>
    <tr>
        <td align="center">
            <table
                width="600"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width:100%;
                    max-width:600px;
                    background:#ffffff;
                    border-radius:12px;
                    overflow:hidden;
                    box-shadow:0 3px 15px rgba(16,24,40,0.08);
                "
            >
                <tr>
                    <td
                        align="center"
                        style="
                            background:#101828;
                            padding:15px 10px;
                        "
                    >
                        <img
                            src="{{ $message->embed(public_path('assets/images/logo.png')) }}"
                            alt="SLW Travel"
                            width="180"
                            style="
                                display:block;
                                width:180px;
                                max-width:100%;
                                height:auto;
                                margin:0 auto;
                                border:0;
                            "
                        >
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                            height:5px;
                            background:#16a34a;
                            font-size:0;
                            line-height:0;
                        "
                    >
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td style="padding:40px 35px;">
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                        >
                            <tr>
                                <td align="center">

                                    <div style="
                                        width:62px;
                                        height:62px;
                                        line-height:62px;
                                        border-radius:50%;
                                        background:#dcfce7;
                                        color:#16a34a;
                                        font-size:30px;
                                        font-weight:bold;
                                        text-align:center;
                                        margin:0 auto 18px;
                                    ">
                                        ✓
                                    </div>

                                </td>
                            </tr>
                        </table>
                        <h1 style="
                            margin:0 0 25px;
                            text-align:center;
                            color:#101828;
                            font-size:26px;
                            line-height:1.3;
                            font-weight:700;
                        ">
                            Agent Account Activated
                        </h1>
                        <p style="
                            margin:0 0 15px;
                            color:#344054;
                            font-size:15px;
                            line-height:1.7;
                        ">
                            Hello
                            <strong>
                                {{ $agent->name ?? 'Agent' }}
                            </strong>,
                        </p>
                        <p style="
                            margin:0 0 25px;
                            color:#667085;
                            font-size:15px;
                            line-height:1.8;
                        ">
                            Your Sun Leisure World B2B Agent account has been
                            successfully activated by the administrator.
                            You can now log in to the SLW B2B Portal and start
                            exploring our tours, transfers, hotels, packages,
                            and other travel services.
                        </p>
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                background:#f8fafc;
                                border:1px solid #eaecf0;
                                border-radius:10px;
                                overflow:hidden;
                            "
                        >
                            <tr>
                                <td
                                    colspan="2"
                                    style="
                                        padding:18px 20px;
                                        border-bottom:1px solid #eaecf0;
                                    "
                                >
                                    <strong style="
                                        color:#101828;
                                        font-size:16px;
                                    ">
                                        Account Details
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="
                                    width:40%;
                                    padding:13px 20px;
                                    color:#667085;
                                    font-size:14px;
                                ">
                                    Agent ID
                                </td>

                                <td style="
                                    padding:13px 20px;
                                    color:#101828;
                                    font-size:14px;
                                    font-weight:600;
                                ">
                                    AGT-{{ $agent->add_agent_id }}
                                </td>
                            </tr>
                            <tr>
                                <td style="
                                    padding:13px 20px;
                                    color:#667085;
                                    font-size:14px;
                                ">
                                    Company
                                </td>

                                <td style="
                                    padding:13px 20px;
                                    color:#101828;
                                    font-size:14px;
                                    font-weight:600;
                                ">
                                    {{ $agent->name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="
                                    padding:13px 20px;
                                    color:#667085;
                                    font-size:14px;
                                ">
                                    Email
                                </td>

                                <td style="
                                    padding:13px 20px;
                                    color:#101828;
                                    font-size:14px;
                                    font-weight:600;
                                    word-break:break-word;
                                ">
                                    {{ $agent->email ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="
                                    padding:13px 20px 18px;
                                    color:#667085;
                                    font-size:14px;
                                ">
                                    Status
                                </td>

                                <td style="
                                    padding:13px 20px 18px;
                                ">
                                    <span style="
                                        display:inline-block;
                                        background:#dcfce7;
                                        color:#15803d;
                                        padding:6px 13px;
                                        border-radius:20px;
                                        font-size:13px;
                                        font-weight:700;
                                    ">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        </table>
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="margin-top:30px;"
                        >
                            <tr>
                                <td align="center">

                                    <a
                                        href="https://slw.travel/login"
                                        target="_blank"
                                        style="
                                            display:inline-block;
                                            background:#ff5a3d;
                                            color:#ffffff;
                                            text-decoration:none;
                                            padding:13px 28px;
                                            border-radius:7px;
                                            font-size:15px;
                                            font-weight:700;
                                        "
                                    >
                                        Login to SLW Travel
                                    </a>

                                </td>
                            </tr>
                        </table>
                        <p style="
                            margin:28px 0 0;
                            color:#667085;
                            font-size:14px;
                            line-height:1.7;
                        ">
                            If you face any technical issues while accessing
                            or using the portal, please contact our technical
                            support team at
                            <a
                                href="mailto:tech@sunleisureworld.com"
                                style="
                                    color:#ff5a3d;
                                    font-weight:700;
                                    text-decoration:none;
                                "
                            >
                                tech@sunleisureworld.com
                            </a>.
                        </p>
                        <p style="
                            margin:25px 0 0;
                            color:#667085;
                            font-size:14px;
                            line-height:1.7;
                        ">
                            Regards,<br>

                            <strong style="color:#101828;">
                                Sun Leisure World Team
                            </strong>
                        </p>

                    </td>
                </tr>
                <tr>
                    <td style="
                        background:#f8fafc;
                        border-top:1px solid #eaecf0;
                        padding:25px 30px;
                        text-align:center;
                    ">

                        <strong style="
                            display:block;
                            color:#101828;
                            font-size:15px;
                        ">
                            Sun Leisure World Corporation
                        </strong>

                        <p style="
                            margin:8px 0 0;
                            color:#667085;
                            font-size:13px;
                        ">
                            <a
                                href="mailto:tech@sunleisureworld.com"
                                style="
                                    color:#667085;
                                    text-decoration:none;
                                "
                            >
                                tech@sunleisureworld.com
                            </a>
                            &nbsp;|&nbsp;
                            <a
                                href="https://slw.travel/"
                                target="_blank"
                                style="
                                    color:#667085;
                                    text-decoration:none;
                                "
                            >
                                slw.travel
                            </a>
                        </p>

                        <p style="
                            margin:10px 0 0;
                            color:#98a2b3;
                            font-size:12px;
                            line-height:1.6;
                        ">
                            This is an automated email. Please do not reply
                            directly to this message.
                        </p>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
