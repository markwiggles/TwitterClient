<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


    
   $randPos = rand(0,5);
   $randNeu = rand(0,5);
   $randNeg = rand(0,5);

   $randT1 = rand(0,20)-10/1000;
   $randT2 = rand(0,20)-10/1000;
   $randT3 = rand(0,20)-10/1000;
   $randT4 = rand(0,20)-10/1000;
   $randT5 = rand(0,20)-10/1000;
   
 
$chartData = array(
    'positive' => $randPos,
    'neutral' => $randNeu,
    'negative' => $randNeg,
    't1' => $randT1,
    't2' => $randT2,
    't3' => $randT3,
    't4' => $randT4,
    't5' => $randT5
    );

print json_encode($chartData);
?>
