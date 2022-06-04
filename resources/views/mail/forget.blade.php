<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-xs-12">
                Good Day, <br>
                Reset your password <a href="http://localhost:300/reset/{{$data}}">
                    Reset Password
                </a>
                <br>
                Token : {{ $data }}
            </div>
        </div>
    </div>
</body>
</html>