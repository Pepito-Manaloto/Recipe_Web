<?php
require_once(__DIR__ . "/Database.php");

abstract class RecipeView
{   
    protected $title;
    protected $category;
    protected $preparationTime;
    protected $description;
    protected $servings;
    
    protected $ingredients = array();
    protected $quantity = array();
    protected $measurement = array();
    protected $ingredient = array();
    protected $comment = array();
    protected $instructions = array();
    
    public function getAllRecipeTitle($lastUpdated)
    {
        global $db;
        $mysqli = $db->getMySQLiConnection();
        
        $query = "CALL get_all_recipe_title(?);";

        if($stmt = $mysqli->prepare($query))      
        {
            $stmt->bind_param("s", $lastUpdated);
            $stmt->execute();
            $data = array();

            if($result = $stmt->get_result())
            {
                $data[] = mysqli_fetch_all($result);
                $result->free_result();
            }
        }

        $db->closeConnection();

        return $data;
    }

    public function getRecipe($recipe)
    {
        global $db;
    
        $this->title = $recipe;

        $mysqli = $db->getMySQLiConnection();
        
        $query = "CALL get_recipe(?);"; 
    
        if($stmt = $mysqli->prepare($query))      
        {
            $stmt->bind_param("s", $recipe);
            $stmt->execute();
            
            $data = array();
            do 
            {
                if($result = $stmt->get_result()) // Retrieves result 3 times
                {
                    $data[] = mysqli_fetch_all($result); // $data[QueryIndex][RowIndexOfTheQuery][ColumnIndexOfRow]
                    $result->free_result();
                }
            }while($stmt->more_results() && $stmt->next_result());

            if(count($data[0]) < 1)
            {
                header("Location: http://{$_SERVER['HTTP_HOST']}/Recipe/Browse_Recipe/?type=My");
                exit;
            }

            // First select query
            $this->category = $data[0][0][0];
            $this->preparationTime = $data[0][0][1];
            $this->description = $data[0][0][2];
            $this->servings = $data[0][0][3];

            // Second select query
            for($i=0; $i < count($data[1]); $i++)
            {
                $this->quantity[] = $data[1][$i][0] + 0; // truncates trailing decimal zeros
                $this->measurement[] = $data[1][$i][1];
                $this->ingredient[] = $data[1][$i][2];
                $this->comment[] =  $data[1][$i][3];
            }

            // Third select query
            for($i=0; $i < count($data[2]); $i++)
            {
                $this->instructions[] = $data[2][$i][0];
            }
            
            //combine all ingredients to one array.
            $size = count($this->quantity);
            for($i = 0; $i<$size; $i++) //String treats $this->variable as a variable, but treats [$i] as String. So they must be enclosed in a {} to be treated as an array variable.
            {
                if( empty($this->comment[$i]) )
                {
                    $this->ingredients[] = "{$this->quantity[$i]} {$this->measurement[$i]} {$this->ingredient[$i]}";
                }
                else
                {
                    $this->ingredients[] = "{$this->quantity[$i]} {$this->measurement[$i]} {$this->ingredient[$i]} ({$this->comment[$i]})"; 
                }
            }
        }    

        $db->closeConnection();

        return $data;
    }
    
    abstract protected function showTitle();
    abstract protected function showCategory();
    abstract protected function showPreparationTime();
    abstract protected function showDescription();
    abstract protected function showServings();
    abstract protected function showIngredients();
    abstract protected function showInstructions();
}
?>