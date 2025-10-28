<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Preview del PDF</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .pdf-content {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="pdf-content">
        <?= $html ?>
    </div>
</body>
</html>
