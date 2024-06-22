<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400&display=swap');


        * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .section {
            width: 100%;
            min-height: 100vh;
            background-color: #ddd;
        }

        .container {
            width: 80%;
            display: block;
            margin: auto;
            padding-top: 100px;

        }

        .content-section {
            float: left;
            width: 55%;
        }

        .image-section {
            float: right;
            width: 40%;
            margin-top: 80px;
        }

        .image-section img {
            width: 100%;
            height: auto;
        }

        .content-section .title {
            text-transform: uppercase;
            font-size: 28px;
        }

        .content-section .content p {
            margin-top: 10px;
            font-family: sans-serif;
            font-size: 18px;
            line-height: 1.5;
            text-align: justify;
        }

        .content-section.content .button {
            margin-top: 30px;
        }

        .content-section.content .button a {
            background-color: #3d3d3d;
            padding: 12px 40px;
            text-decoration: none;
            color: #fff;
            font-size: 25px;
            letter-spacing: 1.5px;
        }

        .content-section.content .button a:hover {
            background-color: #a52a2a;
            color: #fff;
        }

        .content-section .social {
            margin: 40px 40px;
        }

        .content-section .social i {
            color: #a52a2a;
            font-size: 30px;
            padding: 0px 10px;
       }
       .content-section .social i:hover{
        color:#3d3d3d;
       }
       @media screen and (max-width:768px){
        .container{
            width:80%;
            display: block;
            margin:auto;
            padding-top:50px;
        }
        .content-section{
            float: none;
            width: 100%;
            display: block;
            margin: auto;
        }
        .image-section{
            float: none;
            width:100%;
           
        }
        .image-section img{
            width: 100%;
            height: auto;
            

        }
        .content-section .title{
            text-align: center;
            font-size: 19px;
        }
        .content-section .content .button{
            text-align: cneter;
        }
        .content-section.content .button a{
            padding: 9px 30px;
        }
        .content-section.social{
            text-align: center;
        }
        .back-link {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 24px;
    position: absolute;
    top: 20px;
    left: 20px;
}
 }

    </style>
</head>

<body>

    <div class="section">
        <div class="container">
            <div class="content-section">
                <div class="title">
                    <h1> About Us</h1>
                </div>
                <div class="content">

                    <p>"Welcome to our online mock test platform, designed to help students excel in their entrance exams. 
                        At Online Mock Test, our mission is to provide aspiring candidates with comprehensive and effective preparation resources for exams such as CMAT exams.
                         Our team of dedicated professionals has meticulously crafted a vast question bank, ensuring that students have access to a diverse range of practice materials. 
                         With timed tests and instant result our platform empowers students to gauge their progress, identify areas for improvement, and enhance their exam performance.
                          We are committed to maintaining the highest standards of privacy and security, and we have implemented robust measures to safeguard user data. 
                          As passionate educators, we take pride in offering a user-friendly and reliable platform that equips students with the confidence and skills needed to succeed in their exams. 
                          Join us on this journey, and together, let's unlock your true potential.</p>
                    <div class="button">
                        <a href="">Read More</a>
                    </div>
                </div>
                <div class="social">
                    <a href=""><i class="fab fa-facebook-f"></i></a>
                    <a href=""><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="image-section">
                <img src="css/img.jpg">
            </div>
        </div>
    </div>

</body>

</html>