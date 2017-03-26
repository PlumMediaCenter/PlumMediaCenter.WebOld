<?php

class LibraryController extends ApiController {
    
   public function Generate(){
       Db::Install();
       return true;
   }

}

$router->map('GET', 'api/library/generate', 'LibraryController#Generate');



