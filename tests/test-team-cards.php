<?php

/**
* Test Utilities
*/

require_once "./info-cards.php";


class IC_InfoCardsTest extends WP_UnitTestCase
{



    public function test_methods()
    {
   		$this->assertTrue( is_plugin_active('info-cards/info-cards.php') );
   		
      
        /**
         *  Test function info_cards_activate()
         **/
         
        ob_start();
        info_cards_activate();
        $output = ob_get_contents();
        $this->assertTrue("" === $output);
        ob_end_clean();
        
        /**
         *  Test info_cards_deactivate(){
         **/
         
   	    ob_start();
        info_cards_deactivate();
        $output = ob_get_contents();
        $this->assertTrue("" === $output);
        ob_end_clean();
        
   
    }

}

