<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Payment Invitation</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f1e5; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f1e5; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 18px 50px rgba(47,62,42,0.08);">
                    <tr>
                        <td style="background-color:#166534; padding:32px 40px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:24px; margin:0; font-weight:600;">You're invited to split a payment!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 40px;">
                            <p style="font-size:16px; color:#374151; margin:0 0 16px;">
                                <strong>{{ $initiatorName }}</strong> invited you to split the cost of a homestay booking.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fbf7ee; border-radius:16px; padding:20px; margin:16px 0;">
                                <tr>
                                    <td>
                                        <p style="font-size:14px; color:#6b7280; margin:0 0 4px;">Homestay</p>
                                        <p style="font-size:18px; color:#111827; font-weight:600; margin:0 0 12px;">{{ $booking['homestay_name'] }}</p>

                                        <p style="font-size:14px; color:#6b7280; margin:0 0 4px;">Location</p>
                                        <p style="font-size:14px; color:#374151; margin:0 0 12px;">{{ $booking['location'] }}</p>

                                        <p style="font-size:14px; color:#6b7280; margin:0 0 4px;">Dates</p>
                                        <p style="font-size:14px; color:#374151; margin:0 0 12px;">
                                            {{ \Carbon\Carbon::parse($booking['check_in'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($booking['check_out'])->format('d M Y') }}
                                        </p>

                                        <p style="font-size:14px; color:#6b7280; margin:0 0 4px;">Your share</p>
                                        <p style="font-size:24px; color:#166534; font-weight:700; margin:0;">RM {{ number_format($shareAmount, 2) }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:14px; color:#dc2626; margin:16px 0 24px;">
                                <strong>Important:</strong> All members must pay within <strong>1 hour</strong>. If not everyone pays in time, the booking will be automatically cancelled.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('group-payment.show', $token) }}"
                                            style="display:inline-block; background-color:#16a34a; color:#ffffff; font-size:16px; font-weight:600; text-decoration:none; padding:14px 40px; border-radius:9999px;">
                                            Join & Pay Now
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb; padding:20px 40px; text-align:center; border-top:1px solid #eee5d7;">
                            <p style="font-size:12px; color:#9ca3af; margin:0;">
                                This invitation was sent via RumaRehat. If you don't have an account, you'll be prompted to create one.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
