
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<?php

$style = [
    // Layout ------------------------------ /

    'body' => 'margin: 0; padding: 0; width: 100%; background-color: #656565;',
    'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: #656565;',

    // Masthead ----------------------- /

    'email-masthead' => 'padding: 10px 0 5px; text-align: center;',
    'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;',

    'email-body' => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px solid #EDEFF2; background-color: #FFF;',
    'email-body_inner' => 'width: auto; max-width: 800px; margin: 0 auto; padding: 0;',
    'email-body_cell' => 'padding: 15px;',

    'email-footer' => 'width: auto; max-width: 800px; margin: 0 auto; padding: 0; text-align: center;',
    'email-footer_cell' => 'color: #AEAEAE; padding: 15px; text-align: center;',

    // Body ------------------------------ /

    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_sub' => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

    // Type ------------------------------ /

    'anchor' => 'color: #fff; word-break: break-all;',
    'header-1' => 'margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
    'paragraph' => 'margin-top: 0; margin-bottom: 0; color: #74787E; font-size: 16px; line-height: 1.5em;',
    'paragraph-sub' => 'margin-top: 0; color: #fff; font-size: 12px; line-height: 1.5em;',
    'paragraph-center' => 'text-align: center;',
    'boldfont' => 'color:black',
    // Buttons ------------------------------ /

    'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: #3869D4; border-radius: 3px; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none;',

    'button--green' => 'background-color: #22BC66;',
    'button--red' => 'background-color: #dc4d2f;',
    'button--blue' => 'background-color: #3869D4;',
];
?>

<?php $fontFamily = 'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;'; ?>

<body style="{{ $style['body'] }}">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="{{ $style['email-wrapper'] }}" align="center">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="{{ $style['email-masthead'] }}">
                            <a style="{{ $fontFamily }} {{ $style['email-masthead_name'] }}" href="{{ url('/') }}" target="_blank">
                                {!! Html::image('/frontend/images/white_logo.png', config('app.name'), ['width'=> '68', 'height' => '55']) !!}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="{{ $style['email-body'] }}" width="100%">
                            <table style="{{ $style['email-body_inner'] }}" align="center" width="800" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                                        <h1 style="{{ $style['header-1'] }}">
                                            Hello {{$name}},
                                        </h1>
                                        <p style="{{ $style['paragraph'] }}">
                                            {{-- Update code by Purvesh --}}
                                            {{-- You have been received a reservation request through Five Star Sitters. Please log in to confirm, or reject, the request.<br><br> --}}

                                            You have received a reservation request through Five Star Sitters. Please log in to confirm or reject the request.<br><br>

                                            Please contact us if you have any questions or concerns.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                                        <p style="{{ $style['paragraph'] }}">
                                            Thank you,<br><b style="{{ $style['boldfont'] }}">The Five Star Sitters Team</b>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="{{ $style['email-footer'] }}" align="center" width="800" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="{{ $fontFamily }} {{ $style['email-footer_cell'] }}">
                                        <p style="{{ $style['paragraph-sub'] }}">
                                            &copy; {{ date('Y') }}
                                            <a style="{{ $style['anchor'] }}" href="{{ url('/') }}" target="_blank">{{config('app.name')}}</a>.
                                            All Rights Reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
