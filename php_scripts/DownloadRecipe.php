<?php require_once(__DIR__ . "/model/Recipe.php");if(isset($_GET['title']) && !empty($_GET['title'])){	Recipe::downloadAsPdf($_GET['title']);}?>