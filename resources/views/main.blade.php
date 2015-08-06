<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Log Envelope</title>
    </head>
    <body style="margin: 0px; padding: 0px;">
        <div style="padding: 10px; background-color: #f4645f; color: white;">
            <h4 style="margin: 0; font-weight: 100;">
                <div style="float:right;">{{ $file }}::{{ $line }}</div>
                <div>[{{ $method }}]: {{ $fullUrl }}</div>
            </h4>
            <h1 style="margin: 0; font-size: 3em;">
                {{ $class }}
                <br>
                {{ $exception }}
            </h1>
        </div>
        <pre style="margin: 0; background: #272727; color: #aaaaaa; font-family: monospace; font-size: 12px; padding: 5px 12px; white-space: pre-wrap; word-break: break-word;">{{ $error }}</pre>
        <div style="padding: 6px; text-align: right; background-color: #f4645f;">
            <h6 style="margin: 0; font-weight: 100; font-family: monospace; font-size: 10px;">developed by Yaro</h6>
        </div>
        
        <table style="border-collapse: collapse;">
            <tbody>
                @foreach ($storage as $caption => $data)
                    @include('log-envelope::storage')
                @endforeach
            </tbody>
        </table>
    </body>
</html>

