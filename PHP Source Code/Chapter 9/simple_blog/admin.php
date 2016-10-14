<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 2);

    /*
     * Include the necessary files
     */
    include_once 'inc/functions.inc.php';
    include_once 'inc/db.inc.php';

    // Open a database connection
    $db = new PDO(DB_INFO, DB_USER, DB_PASS);

    $page = isset($_GET['page']) ? htmlentities(strip_tags($_GET['page'])) : 'blog';

    if(isset($_GET['url']))
    {
        $url = htmlentities(strip_tags($_GET['url']));

        // Check if the entry should be deleted
        if($page == 'delete')
        {
            if(deleteEntry($db, $url)===TRUE)
            {
                header("Location: /simple_blog/");
                exit;
            }
            else
            {
                die("Error deleting the entry!");
                exit;
            }
        }

        // Set the legend of the form
        $legend = "Edit This Entry";

        $e = retrieveEntries($db, $page, $url);
        $id = $e['id'];
        $title = $e['title'];
        $img = $e['image'];
        $entry = $e['entry'];
    }
    else
    {
        // Set the legend
        $legend = "New Entry Submission";

        // Set the variables to null if not editing
        $id = NULL;
        $title = NULL;
        $img = NULL;
        $entry = NULL;
    }
?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="/simple_blog/css/default.css" type="text/css" />
    <title> Simple Blog </title>
</head>

<body>
    <h1> Simple Blog Application </h1>

    <form method="post" 
    	action="/simple_blog/inc/update.inc.php" 
    	enctype="multipart/form-data">
        <fieldset>
            <legend><?php echo $legend ?></legend>
            <label>Title 
                <input type="text" name="title" maxlength="150"
                    value="<?php echo $title ?>" />
            </label>
            <label>Image 
                <input type="file" name="image" />
            </label>
            <label>Entry 
                <textarea name="entry" cols="45" 
                    rows="10"><?php echo $entry ?></textarea>
            </label>
            <input type="hidden" name="id"
                value="<?php echo $id ?>" />
            <input type="hidden" name="page"
                value="<?php echo $page ?>" />
            <input type="submit" name="submit" value="Save Entry" />
            <input type="submit" name="submit" value="Cancel" />
        </fieldset>
    </form>
</body>

</html>