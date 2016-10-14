<?php

function retrieveEntries($db, $page, $url=NULL)
{
    /*
     * If an entry URL was supplied, load the associated entry
     */
    if(isset($url))
    {
        $sql = "SELECT id, page, title, entry
                FROM entries
                WHERE url=?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($url));

        // Save the returned entry array
        $e = $stmt->fetch();

        // Set the fulldisp flag for a single entry
        $fulldisp = 1;
    }

    /*
     * If no entry ID was supplied, load all entry titles for the page
     */
    else
    {
        $sql = "SELECT id, page, title, entry, url
                FROM entries
                WHERE page=?
                ORDER BY created DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($page));
    
        $e = NULL; // Declare the variable to avoid errors

        // Loop through returned results and store as an array
        while($row = $stmt->fetch()) {
            if($page=='blog')
            {
                $e[] = $row;
                $fulldisp = 0;
            }
            else
            {
                $e = $row;
                $fulldisp = 1;
            }
        }

        /*
         * If no entries were returned, display a default
         * message and set the fulldisp flag to display a
         * single entry
         */
        if(!is_array($e))
        {
            $fulldisp = 1;
            $e = array(
                'title' => 'No Entries Yet',
                'entry' => 'This page does not have an entry yet!'
            );
        }
    }

    // Add the $fulldisp flag to the end of the array
    array_push($e, $fulldisp);

    return $e;
}

function sanitizeData($data)
{
    if(!is_array($data))
    {
        return strip_tags($data, "<a>");
    }
    else
    {
        return array_map('sanitizeData', $data);
    }
}

function makeUrl($title)
{
    $patterns = array(
        '/\s+/',
        '/(?!-)\W+/'
    );
    $replacements = array('-', '');
    return preg_replace($patterns, $replacements, strtolower($title));
}

?>