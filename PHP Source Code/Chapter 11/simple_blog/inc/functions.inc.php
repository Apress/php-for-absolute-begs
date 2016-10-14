<?php

function retrieveEntries($db, $page, $url=NULL)
{
    /*
     * If an entry URL was supplied, load the associated entry
     */
    if(isset($url))
    {
        $sql = "SELECT id, page, title, image, entry, created
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
        $sql = "SELECT id, page, title, image, entry, url, created
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

function confirmDelete($db, $url)
{
	$e = retrieveEntries($db, '', $url);

	return <<<FORM
<form action="/simple_blog/admin.php" method="post">
	<fieldset>
		<legend>Are You Sure?</legend>
		<p>Are you sure you want to delete the entry
		"$e[title]"?
		</p>
		<input type="submit" name="submit" value="Yes" />
		<input type="submit" name="submit" value="No" />
		<input type="hidden" name="action" value="delete" />
		<input type="hidden" name="url" value="$url" />
	</fieldset>
</form>
FORM;
}

function deleteEntry($db, $url)
{
    $sql = "DELETE FROM entries
            WHERE url=?
            LIMIT 1";
    $stmt = $db->prepare($sql);
    return $stmt->execute(array($url));
}

function adminLinks($page, $url)
{
    // Format the link to be followed for each option
    $editURL = "/simple_blog/admin/$page/$url";
    $deleteURL = "/simple_blog/admin/delete/$url";

    // Make a hyperlink and add it to an array
    $admin['edit'] = "<a href=\"$editURL\">edit</a>";
    $admin['delete'] = "<a href=\"$deleteURL\">delete</a>";

    return $admin;
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

function formatImage($img=NULL, $alt=NULL)
{
	if(!empty($img))
	{
		return '<img src="'.$img.'" alt="'.$alt.'" />';
	}
	else
	{
		return NULL;
	}
}

function createUserForm()
{
	return <<<FORM
<form action="/simple_blog/inc/update.inc.php" method="post">
	<fieldset>
		<legend>Create a New Administrator</legend>
		<label>Username
			<input type="text" name="username" maxlength="75" />
		</label>
		<label>Password
			<input type="password" name="password" />
		</label>
		<input type="submit" name="submit" value="Create" />
		<input type="submit" name="submit" value="Cancel" />
		<input type="hidden" name="action" value="createuser" />
	</fieldset>
</form>
FORM;
}

?>