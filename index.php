<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <style>
        .container {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
            background-color: #f9f9f9;
            margin-left: 200px;
            margin-right: 200px;
        }

        .container pre {
            white-space: pre-wrap;
        }

        h2 {
            text-align: center;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
        }

        form {
            margin: 20px;
            text-align: center;
        }

        input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 200px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        div.ex2 {
            max-width: 500px;
            margin: auto;
            border: 3px solid #73AD21;
        }
    </style>
</head>
<body>
    <h2>Blog Post Writer</h2>
    <form method="post">
        <input type="text" placeholder="TYPE HERE" name="str" required>
        <input type="submit" name="submit" value="Enter">
    </form>
<!-- End HTML,CSS Part -->
<div class="container">
    <?php
    function convert_to_wp_block_markup($text) {
        // Escape special characters
        $text = htmlspecialchars($text);

        // Wrap the text in a paragraph block
        $markup = '<!-- wp:paragraph -->';
        $markup .= '<p>' . $text . '</p>';
        $markup .= '<!-- /wp:paragraph -->';

        return $markup;
    }

    
    if (isset($_POST['str'])) {
        $ch = curl_init();
        $str = $_POST['str'];
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $search_Questions = [
            'What is ' . $str . '?',
            'Why ' . $str . '?',
            'How does ' . $str . ' work?',
            $str . ' architecture ?',
            'How to install and configure ' . $str .'?',
            'Basic tutorial of ' . $str .'?',
        ];

        $results = [];

        foreach ($search_Questions as $Question) {
            $postdata = array(
                "model" => "text-davinci-001",
                "prompt" => $Question,
                "temperature" => 0.4,
                "max_tokens" => 2000,
                "top_p" => 1,
                "frequency_penalty" => 0,
                "presence_penalty" => 0
            );
            $postdata = json_encode($postdata);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: Bearer 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';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error: ' . curl_error($ch);
            }

            $result = json_decode($result, true);
            if (isset($result['choices']) && !empty($result['choices'])) {
                $answer = $result['choices'][0]['text'];
                $results[] = array('Question' => $Question, 'answer' => $answer);
            } else {
                // echo 'Error: Invalid API response';
            }
        }

        curl_close($ch);

        // Display the question-answer pairs
        foreach ($results as $result) {
            echo '<h3>' . $result['Question'] . '</h3>';
            echo convert_to_wp_block_markup($result['answer']); // Convert the answer to WordPress block markup
            echo '';
        }
        
        //-------------------------------------------Start WordPress coding part ----------------------------------------------------
        // Create a post in WordPress
        $username = 'admin';
        $password = 'Kumar@Cotocus01';
        $rest_api_url = "http://localhost/wordpress/wp-json/wp/v2/posts";

        $data = array(
            'title'    =>  $str, // Use the first question as the post title
            'content'  => '', // Initialize the content variable
            'status'   => 'publish',
        );

        // Generate the content using the results
        
        $content = ''; // Initialize the $content variable
        foreach ($results as $result) {
            // '<!-- wp:paragraph -->'
       
            $content .= '<!-- wp:heading {"level":2} --> <h2>'. $result['Question'] .'</h1><!-- /wp:heading --> ';
            $content .= convert_to_wp_block_markup($result['answer']); // Convert the answer to WordPress block markup
            $content .= '';
        }

        $data['content'] = $content;

        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rest_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'Authorization: Basic ' . base64_encode($username . ':' . $password),
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        curl_close($ch);

        if ($result) {
            echo 'Post created successfully in WordPress!';
        } else {
            echo 'Error creating the post in WordPress.';
        }
    }
    ?>
</div>



