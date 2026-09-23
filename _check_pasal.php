<?php
require __DIR__.'/vendor/autoload.php';
foreach (App\Data\ContractTemplates::all() as $k=>$t){
  echo "=== $k ===\n";
  $i=0;
  foreach (($t['body_content']['isi']??[]) as $pasal){
    $i++;
    echo $i.". ".($pasal['judul']??'-')."\n";
  }
  echo "\n";
}
