<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #efefef;
            font-family: sans - serif;
        }

        .contact-box {
            width: 500 px;
            background-color: #fff;
            box-shadow: 0 0 20 px 0 #999;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            position: absolute;
        }

        form {
            margin: 35px;
        }

        .input-field {
            width: 400px;
            height: 40px;
            margin-top: 20px;
            padding-left: 10px;
            padding-right: 10px;
            border: 1px solid #777;
            border-radius: 14px;
            outline: none;
        }
        .textarea-field{
            height:150px;
            padding-top:10px;
        }
        .btn{
            border-radius: 20px;
            color:#fff ;
            margin-top: 18px;
            padding: 10px;
            background-color: #47c35a;
            font-size: 12px;
            border:none;
            cursor:pointer;
        }
        .back-link {
    color: black;
    text-decoration: none;
    font-weight: bold;
    font-size: 24px;
    position: absolute;
    top: 20px;
    left: 20px;
}



    </style>
</head>

<body>
<a href="javascript:history.back()" class="back-link">&#x2190;</a>
    <div class="contact-box">
        <form>
            <input type="text" class="input-field" placeholder="your Name">
            <input type="email" class="input-field" placeholder="your Email">
            <input type="text" class="input-field" placeholder="Subject">
            <textarea type="text" class="input-field textarea-field"placeholder="Your Message"></textarea><br>
            <button  type = "button" class = "btn">Send Message</button>
        </form>


    </div>

</body>

</html>