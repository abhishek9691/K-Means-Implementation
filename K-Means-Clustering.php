<?php
    ob_start();
    $k = 5;    
    //ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 6000000000);
    $lines = file('mnist_data.txt');
    $digits = file('mnist_labels.txt');
    $split_lines = array();
    $num_lines = count($lines); 
    foreach($lines as $line_num => $line){
        //echo $line."<hr />"; 
        $split_lines[] = explode(" ", $line); 
    }
    
    $counter = 0; 
    foreach($digits as $line_num => $line){
        $split_lines[$counter][784] = $line;
        //print_r($split_lines[$counter]);
        $counter++;        
    }    
    //echo $split_lines[$counter][784]."<br />";
    //turns out we have 784 characters per row in the data
    //use 0-based indexing for the arrays 
    //list of centroids initially picked, can have only k centroids 
    $centroids = array();
    //2D array of the assignments of each row to a centroid
    //a 1 to 1 correspondence of the centroids array to the assignments array
    //$assignments[centroid number] = array(), and the vectors are pushed onto the array
    $cluster = array();
    $prev_distances = array();
    //initialize the centroid array, clusters, and previous distances
    for($i = 0; $i < $k; $i++){
        $centroids[$i] = $split_lines[rand(0, $num_lines-1)];
        $cluster[$i] = array();
        $prev_distances[$i] = 0.0; 
    }
    $size = count($split_lines);
    $iterations = 0; 
    while(true){        
        foreach($split_lines as $key => $line){
            $min_c = PHP_INT_MAX;
            $centroid_c = -1; 
            foreach($centroids as $keyC => $centroid){
                $dist = compute_distance($line, $centroid);
                if($dist < $min_c){
                    $min_c = $dist;
                    $centroid_c = $keyC;                    
                }
            }
            $cluster[$centroid_c][] = $line; 
        }
        $new_centroids = array(); 
        foreach($cluster as $key => $clust){
            $new_centroid = compute_average($clust);
            $new_centroids[] = $new_centroid;
        }        
        $converged = true;
        for($i = 0; $i < $k; $i++){
            $dist = compute_distance($new_centroids[$i], $centroids[$i]);
            //echo "<h1>".$dist."</h1><br />";
            if($dist > 0.0){
                $converged = false;
            }
        }        
        $iterations++;
        echo $iterations."<br />";
        ob_flush();
        flush(); 
        if($converged){
            break;
        } else {
            //need to reassign centroids now
            for($i = 0; $i < $k; $i++){
                $centroids[$i] = $new_centroids[$i];
                $cluster[$i] = array(); 
            }
        }
    }
    echo "Number iterations: ".$iterations."<br />Common Digit: ";
    ob_flush();
    flush(); 
    //now need most common digit in the cluster, and count what is different from most common digit
    $common_digits = array(); 
    foreach($cluster as $key => $c_check){
        $cd = getCommonDigit($c_check);
        echo $key.": ".$cd.", ";
        $common_digits[$key] = $cd; 
    }
    echo "<hr />";
    foreach($cluster as $key => $c_check){
        print_r(getNumWrong($c_check, $common_digits[$key], $k, $key));
    }
    echo "<hr />"; 
    foreach($cluster as $key => $c_check){
        print_r(getTotal($c_check, $common_digits[$key], $k, $key));
    }
    
function getNumWrong($cluster, $digit, $k, $clust_num){
    $digits = array();
    for($i = 0; $i < $k; $i++){
        $digits[$i] = 0; 
    }
    foreach($cluster as $key => $pt){
        if(intval($pt[784]) != intval($digit)){
            $digits[$clust_num] = $digits[$clust_num] + 1; 
        }
    }
    return $digits; 
}

function getTotal($cluster, $digit, $k, $clust_num){
    $digits = array();
    for($i = 0; $i < $k; $i++){
        $digits[$i] = 0; 
    }
    foreach($cluster as $key => $pt){        
        $digits[$pt[784]] = $digits[$pt[784]] + 1;         
    }
    return $digits; 
}

function getCommonDigit($cluster){
    $digits = array();
    for($i = 0; $i < 256; $i++){
        $digits[$i] = 0; 
    }
    foreach($cluster as $key => $line){
        $digit = intval($line[784]);
        $digits[$digit] = $digits[$digit] + 1; 
    }
    $max = 0;
    $max_count = 0; 
    foreach($digits as $digit => $count){
        if($count > $max_count){
            $max_count = $count;
            $max = $digit; 
        }
    }
    return $max; 
}

function compute_distance($vec1, $vec2){
    $sum = 0.0; 
    for($i = 0; $i < 784; $i++){
        $difference = intval($vec1[$i]) - intval($vec2[$i]);
        $product = $difference * $difference;
        $sum += $product; 
    }
    return sqrt($sum); 
}

function compute_average($arrays){
    //$size_vec = count($arrays[0]);//size of each vector
    $num_arrays = count($arrays); //number of arrays in the input parameter  
    $new_centroid = array();
    //echo $num_arrays."<br />"; 
    if($num_arrays == 0)
        return $new_centroid; 
    for($i = 0; $i < 784; $i++){
        $sum = 0.0;
        for($j = 0; $j < $num_arrays; $j++){
            $sum += intval($arrays[$j][$i]);
            //echo count($arrays[$j])." ";
        }
        //echo "<br />";
        $avg = $sum / $num_arrays;
        $new_centroid[] = $avg; 
    }
    return $new_centroid; 
}
?>