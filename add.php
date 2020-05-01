<!DOCTYPE html>

<html lang="en">
<?php
include("includes/init.php");
include("includes/head.php");
?>

<body>


  <?php
  $about_css = "inactive";
  $submit_css = "active";
  include("includes/header.php");

  function filter_text($input, $output)
  {
    if (!empty($input)) {
      return filter_var($input, FILTER_SANITIZE_STRING);
    } else {
      return $output;
    }
  }

  function get_tags()
  {
    $tags = array();
    if (!empty($_POST['class'])) {
      array_push($tags, intval($_POST['class']));
    }
    $mediums = $_POST['medium'];
    if (!empty($mediums)) {
      foreach ($mediums as $medium) {
        array_push($tags, intval($medium));
      }
    }
    return $tags;
  }

  function insert_image_tags($tags, $img_id, $db)
  {
    foreach ($tags as $tag) {
      $sql = "INSERT INTO image_tags (image_id, tag_id) VALUES (:image_id, :tag_id);";
      $params = array(
        ":image_id" => $img_id,
        ":tag_id" => $tag
      );
      exec_sql_query($db, $sql, $params);
    }
  }

  if (isset($_POST['upload_submit'])) {
    $upload_info = $_FILES["art_file"];
    if ($upload_info['error'] == UPLOAD_ERR_OK) { //successful upload
      // filter inputs
      $file_name = basename($upload_info['name']);
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
      $art_title = ucwords(filter_text($_POST['title'], "Untitled"));
      $artist_name = ucwords(filter_text($_POST['artist'], "Anonymous"));
      $description = ucfirst(filter_text($_POST['description'], ""));
      $contact = (!empty($_POST['portfolio']) ? filter_var($_POST['portfolio'], FILTER_SANITIZE_URL) : NULL);
      $art_width = (!empty($_POST['width']) ? filter_var($_POST['width'], FILTER_SANITIZE_NUMBER_FLOAT) : NULL);
      $art_height = (!empty($_POST['height']) ? filter_var($_POST['height'], FILTER_SANITIZE_NUMBER_FLOAT) : NULL);
      // insert info into db
      $artist_sql = "INSERT INTO artists (name) VALUES (:artist_name) ";
      $artist_params = array(':artist_name' => $artist_name);
      exec_sql_query($db, $artist_sql, $artist_params);
      $artist_id = $db->lastInsertId("id");

      $img_sql = "INSERT INTO images (file_name, file_ext, artist_id, title, width, height, description, contact) VALUES (:file_name, :file_ext, :artist_id, :title, :width, :height, :description, :contact)";
      $img_params = array(
        ':file_name' => $file_name,
        ':file_ext' => $file_ext,
        ':artist_id' => $artist_id,
        ":title" => $art_title,
        ':width' => $art_width,
        ':height' => $art_height,
        ':description' => $description,
        ':contact' => $contact
      );
      exec_sql_query($db, $img_sql, $img_params);
      $img_id = $db->lastInsertId("id");
      $new_path = "uploads/images/" . $img_id . "." . $file_ext;
      move_uploaded_file($upload_info["tmp_name"], $new_path);

      // add image tags
      $tags = get_tags();
      insert_image_tags($tags, $img_id, $db);
    } else {
      // deal with fail to upload
    }
  }

  ?>

  <div id="addformwrapper">
    <h2>Submit Your Artwork</h2>

    <form id="addform" method="POST" action="add.php" enctype="multipart/form-data" novalidate>
      <div class="forminput">
        <input type="hidden" name="MAX_FILE_SIZE" VALUE="1000000" />
        <label for="art_file">Upload image</label>
        <input type="file" accept="image/*" name="art_file" />
      </div>
      <div class="forminput">
        <label for="title">Title</label>
        <input type="text" name="title" />
      </div>
      <div class="forminput">
        <label for="artist">Artist</label>
        <input type="text" name="artist" />
      </div>
      <div class="forminput">
        <label for="portfolio">Artist's portfolio</label>
        <input type="url" name="portfolio" />
      </div>
      <div class="forminput">
        <label>Size (inches)</label>
        <input type="number" name="width" class="inline-block smallinput" placeholder="Width" step="0.5" />
        <span> x </span>
        <input type="number" name="height" class="inline-block smallinput" placeholder="Height" step="0.5" />
      </div>

      <div class="forminput" id="tags">
        <div class="inline-block" id="class">
          <label>Class</label>
          <div class="radio-row">
            <input type="radio" name="class" id="ap" value="1" />
            <label for="ap" class="radiolabel">AP Studio Art</label>
          </div>
          <div class="radio-row">
            <input type="radio" name="class" id="aah" value="2" />
            <label for="aah" class="radiolabel">Advanced Art Honors</label>
          </div>
          <div class="radio-row">
            <input type="radio" name="class" id="vis1" value="3" />
            <label for="vis1" class="radiolabel">Visual Art 1</label>
          </div>
          <div class="radio-row">
            <input type="radio" name="class" id="vis2" value="4" />
            <label for="vis2" class="radiolabel">Visual Art 2</label>
          </div>
          <div class="radio-row">
            <input type="radio" name="class" id="foundations" value="5" />
            <label for="foundations" class="radiolabel">Foundations of Art</label>
          </div>
          <div class="radio-row">
            <input type="radio" name="class" id="painting" value="6" />
            <label for="painting" class="radiolabel">Intro to Painting</label>
          </div>

        </div>

        <div class=" inline-block" id="medium">
          <label>Medium</label>
          <div class="checkbox-row">
            <input type="checkbox" id="photography" name="medium[]" value="7">
            <label for="photography" class="radiolabel">Photography</label>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="acrylic" name="medium[]" value="8">
            <label for="acrylic" class="radiolabel">Acrylic</label>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="watercolor" name="medium[]" value="9">
            <label for="watercolor" class="radiolabel">Watercolor</label>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="pencil" name="medium[]" value="10">
            <label for="pencil" class="radiolabel">Pencil</label>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="ink" name="medium[]" value="11">
            <label for="ink" class="radiolabel">Ink</label>
          </div>

        </div>
      </div>
      <div class="forminput">
        <label for="description">Description or Artist's Statement</label>
        <textarea name="description" rows="6" cols="50"></textarea>
      </div>

      <div class="forminput">
        <input type="submit" name="upload_submit" />
      </div>
    </form>
  </div>

</body>

</html>
