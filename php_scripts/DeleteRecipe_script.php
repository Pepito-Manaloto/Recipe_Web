<?php     require_once(__DIR__ . "/model/Recipe.php");        $recipe = new Recipe();        if($recipe->delete())    {        $recipe->commit();    }        $recipe->closeDatabaseConnection();?>