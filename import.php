<?php

/*
**************************************************************************************************************************
** CORAL Resources Module v. 1.2
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/


session_start();

include_once 'directory.php';

//print header
$pageTitle='Resources import';
include 'templates/header.php';

?><div id="importPage"><h1>CSV File import</h1><?php

// CSV configuration
$required_columns = array('titleText' => 0, 'resourceURL' => 0, 'resourceAltURL' => 0, 'isbnOrISSN' => 0, 'providerText' => 0 , 'parentResource' => 0);

if ($_POST['submit']) {
  $delimiter = $_POST['delimiter'];
  $uploaddir = 'attachments/';
  $uploadfile = $uploaddir . basename($_FILES['uploadFile']['name']);
  if (move_uploaded_file($_FILES['uploadFile']['tmp_name'], $uploadfile)) {  
    print '<p>The file has been successfully uploaded.</p>';
  
  // Let's analyze this file
  if (($handle = fopen($uploadfile, "r")) !== FALSE) {
    if (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
      $columns_ok = true;
      foreach ($data as $key => $value) {
        $available_columns[$value] = $key;
      } 
    } else {
      $error = 'Unable to get columns headers from the file';
    }
  } else {
    $error = 'Unable to open the uploaded file';
  }

  } else {
    $error = 'Unable to upload the file';
  }

  if ($error) {
    print "<p>Error: $error.</p>";
  } else {

    print "<p>Please choose columns from your CSV file:</p>";
    print "<form action=\"import.php\" method=\"post\">";
    foreach ($required_columns as $rkey => $rvalue) {
      print "<label for=\"$rkey\">" . $rkey . "</label><select name=\"$rkey\">";
      print '<option value=""></option>';
      foreach ($available_columns as $akey => $avalue) {
        print "<option value=\"$avalue\"";
        if ($rkey == $akey) print ' selected="selected"';
        print ">$akey</option>";
      } 
      print '</select><br />';
    }
    print "<input type=\"hidden\" name=\"delimiter\" value=\"$delimiter\" />";
    print "<input type=\"hidden\" name=\"uploadfile\" value=\"$uploadfile\" />";
    print "<input type=\"submit\" name=\"matchsubmit\" id=\"matchsubmit\" /></form>";
  }

// Process
} elseif ($_POST['matchsubmit']) {

  $delimiter = $_POST['delimiter'];
  $deduping_config = explode(',', $config->settings->importISBNDedupingColumns); 
  $uploadfile = $_POST['uploadfile'];
   // Let's analyze this file
  if (($handle = fopen($uploadfile, "r")) !== FALSE) {
    $row = 0;
    $inserted = 0;
    $parentInserted = 0;
    $parentAttached = 0;
    while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
      // Getting column names again for deduping
      if ($row == 0) {
        print "<h2>Settings</h2>";
        print "<p>Deduping isbnOrISSN on the following columns: " ;
        foreach ($data as $key => $value) {
          if (in_array($value, $deduping_config)) {
            $deduping_columns[] = $key;
            print $value . " ";
          }
        } 
        print ".</p>";
      } else {

        // Deduping
        unset($deduping_values);
        $resource = new Resource(); 

        foreach ($deduping_columns as $value) {
          $deduping_values[] = $data[$value];
        }
        if (count($resource->getResourceByIsbnOrISSN($deduping_values)) == 0) {

          // Convert to UTF-8
          $data = array_map(function($row) { return mb_convert_encoding($row, 'UTF-8'); }, $data);
        
          // Let's insert data
          $resource->createLoginID    = $loginID;
          $resource->createDate       = date( 'Y-m-d' );
          $resource->updateLoginID    = '';
          $resource->updateDate       = '';
          $resource->titleText        = $data[$_POST['titleText']];
          $resource->isbnOrISSN       = $data[$_POST['isbnOrISSN']];
          $resource->resourceURL      = $data[$_POST['resourceURL']];
          $resource->resourceAltURL   = $data[$_POST['resourceAltURL']];
          $resource->providerText     = $data[$_POST['providerText']];
          $resource->statusID         = 1;

          $resource->save();

          // Do we have a parent resource to create?
          if ($_POST['parentResource']) {

            // Search if such parent exists
            $numberOfParents = count($resource->getResourceByTitle($data[$_POST['parentResource']]));
            $parentID = null;
            if ($numberOfParents == 0) {
              // If not, create parent
              $parentResource = new Resource();
              $parentResource->titleText = $data[$_POST['parentResource']];
              $parentResource->save();
              $parentID = $parentResource->resourceID;

              $parentInserted++;

            } elseif ($numberOfParents == 1) {
              // Else, attach the resource to its parent.
              $parentResource = $resource->getResourceByTitle($data[$_POST['parentResource']]);
              $parentID = $parentResource[0]->resourceID;
              
              $parentAttached++; 
            }

            if ($numberOfParents == 0 || $numberOfParents == 1) {
              $resourceRelationship = new ResourceRelationship();
              $resourceRelationship->resourceID = $resource->resourceID;
              $resourceRelationship->relatedResourceID = $parentID;
              $resourceRelationship->relationshipTypeID = '1';  //hardcoded because we're only allowing parent relationships
              $resourceRelationship->save();
            }

          }

          $inserted++;
        } 
      }
      $row++;
    }
    print "<h2>Results</h2>";
    print "<p>$row rows have been processed. $inserted rows have been inserted.</p>";
    print "<p>$parentInserted parents have been created. $parentAttached resources have been attached to an existing parent.</p>";

  }
} else {

          
?>
<form enctype="multipart/form-data" action="import.php" method="post" id="importForm">
  <fieldset>
  <legend>File selection</legend>
  <label for="uploadFile">CSV File</label>
  <input type="file" name="uploadFile" id="uploadFile" />
  </fieldset>
  <fieldset>
  <legend>Import options</legend>
  <label for="CSV delimiter">CSV delimiter</label>
  <select name="delimiter">
    <option value=",">, (comma)</option>
    <option value=";">; (semicolon)</option>
    <option value="|">| (pipe)</option>
  </select>
  </fieldset>
  <input type="submit" name="submit" value="Upload" />
</form>

<?php
}
?>
</div>
<?php
//print footer
include 'templates/footer.php';
?>
