<?php

// Include the functions so we can create a URL
include_once 'functions.inc.php';

// Include the image handling class
include_once 'images.inc.php';

if($_SERVER['REQUEST_METHOD']=='POST'
    && $_POST['submit']=='Save Entry'
    && !empty($_POST['page'])
    && !empty($_POST['title'])
    && !empty($_POST['entry']))
{
    // Create a URL to save in the database
    $url = makeUrl($_POST['title']);

    if(isset($_FILES['image']['tmp_name']))
    {
        try
        {
            // Instantiate the class and set a save dir
            $image = new ImageHandler("/simple_blog/images/");

            // Process the uploaded image and save the returned path
            $img_path = $image->processUploadedImage($_FILES['image']);
        }
        catch(Exception $e)
        {
            // If an error occurred, output our custom error message
        	die($e->getMessage());
        }
    }
    else
    {
        // Avoids a notice if no image was uploaded
    	$img_path = NULL;
    }

    // Include database credentials and connect to the database
    include_once 'db.inc.php';
    $db = new PDO(DB_INFO, DB_USER, DB_PASS);

    // Edit an existing entry
    if(!empty($_POST['id']))
    {
        $sql = "UPDATE entries
                SET title=?, image=?, entry=?, url=?
                WHERE id=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(
            array(
                $_POST['title'],
                $img_path,
                $_POST['entry'],
                $url,
                $_POST['id']
            )
        );
        $stmt->closeCursor();
    }

    // Create a new entry
    else 
    {
        // Save the entry into the database
        $sql = "INSERT INTO entries (page, title, image, entry, url)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(
            array(
                $_POST['page'],
                $_POST['title'],
                $img_path,
                $_POST['entry'],
                $url
            )
        );
        $stmt->closeCursor();
    }

    // Sanitize the page information for use in the success URL
    $page = htmlentities(strip_tags($_POST['page']));

    // Send the user to the new entry
    header('Location: /simple_blog/'.$page.'/'.$url);
    exit;
}

else
{
    header('Location: ../');
    exit;
}

?>