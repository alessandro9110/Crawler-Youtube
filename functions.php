<?



function renderNetwork() {
	
	global $nodes,$edges,$lookup,$no_seeds,$mode;

	
	$nodegdf = "NODI==>name VARCHAR,label VARCHAR,isSeed VARCHAR,seedRank INT,subscriberCount INT,videoCount INT,viewCount(100s) INT\n";
	foreach($nodes as $nodeid => $nodedata) {
		
		$nodedata->statistics->viewCount = round($nodedata->statistics->viewCount / 100);
		
		$nodegdf .= $nodeid . "," . preg_replace("/,|\"|\'/"," ",$nodedata->snippet->title) . "," . $nodedata->isSeed . "," . $nodedata->seedRank . "," . $nodedata->statistics->subscriberCount . "," . $nodedata->statistics->videoCount . "," . $nodedata->statistics->viewCount . "\n";
	}
	
	$edgegdf = "ARCHI==>node1 VARCHAR,node2 VARCHAR,directed BOOLEAN\n";
	foreach($edges as $edgeid => $edgedata) {
		$tmp = explode("_|_|X|_|_",$edgeid);
		if(isset($nodes[$tmp[0]]) && isset($nodes[$tmp[1]])) {
			$edgegdf .= $tmp[0] . "," . $tmp[1] . ",true\n";
		}
	}
	
	$gdf = $nodegdf . $edgegdf;
	$filename = "channelnet_" . $mode . $no_seeds . "_nodes" . count($nodes) . "_" . date("Y_m_d-H_i_s");

	file_put_contents("./data/".$filename.".gdf", $gdf);
	
	echo '<br /><br />The script has created a net with  '.count($nodes).' channels from '.$no_seeds.' seeds.<br /><br />

	your files:<br />
	<a href="./data/'.$filename.'.gdf" download>'.$filename.'.gdf</a><br />';

}

function makeNetworkFromIds($depth) {
	
	global $apikey,$nodes,$edges,$ids,$crawldepth;
	
	echo "<br /><br />getting details for ".count($ids)." channels at depth ".$depth.": ";
	
	$newids = array();
	
	for($i = 0; $i < count($ids); $i++) {
		
		$chid = $ids[$i];
		
		$restquery = "https://www.googleapis.com/youtube/v3/channels?part=brandingSettings,id,snippet,statistics&id=".$chid."&key=".$apikey;
	
		$reply = doAPIRequest($restquery);
		
		//print_r($reply);

		if(isset($reply->items[0])) {
			
			$nodes[$chid] = $reply->items[0];
			$nodes[$chid]->done = false;
			
			if($depth == 0) {
				$nodes[$chid]->isSeed = "yes";
				$nodes[$chid]->seedRank = ($i + 1);
			} else {
				$nodes[$chid]->isSeed = "no";
				$nodes[$chid]->seedRank = "";
			}
		}
		
		
	}



	foreach($nodes as $nodeid => $nodedata) {

		if(isset($nodedata->brandingSettings->channel->featuredChannelsUrls)) {
				
			foreach($nodedata->brandingSettings->channel->featuredChannelsUrls as $featid) {
								
				if(!isset($nodes[$featid])) {
					
					if(!in_array($featid, $newids)) {
						
						$newids[] = $featid;
					}
					
					if($depth < $crawldepth) {
						$edgeid = $nodeid . "_|_|X|_|_" . $featid;
						$edges[$edgeid] = true;
					}
					
				} else {
	
					$edgeid = $nodeid . "_|_|X|_|_" . $featid;
					$edges[$edgeid] = true;
				}
			}	
		}
		
		
		
		if($subscriptions == "on" && $nodedata->done == false) {
	
			$run = true;
			$nextpagetoken = null;
			
			echo $counter . " "; flush(); ob_flush();
			$counter++;
			
			while($run == true) {
		
				$restquery = "https://www.googleapis.com/youtube/v3/subscriptions?part=snippet&channelId=".$nodedata->id."&maxResults=50&key=".$apikey;
				
				if($nextpagetoken != null) {
					$restquery .= "&pageToken=".$nextpagetoken;
				}
				
				$reply = doAPIRequest($restquery);
				
				
				
				//print_r($reply); exit;
				
				if(count($reply->items) > 0) {
									
					foreach($reply->items as $item) {
						
						$featid = $item->snippet->resourceId->channelId;
						
						//print_r($item);
											
						if(!isset($nodes[$featid])) {
							
							if(!in_array($featid, $newids)) {
								
								$newids[] = $featid;
							}
							
							if($depth < $crawldepth) {
								$edgeid = $nodeid . "_|_|X|_|_" . $featid;
								$edges[$edgeid] = true;
							}
							
						} else {
			
							$edgeid = $nodeid . "_|_|X|_|_" . $featid;
							$edges[$edgeid] = true;
						}
	
					}
				
					
					if(isset($reply->nextPageToken) && $reply->nextPageToken != "") {
						
						$nextpagetoken = $reply->nextPageToken;
							
					} else {
						
						$run = false;
					}
				} else {
					
					$run = false;
				}
			}
			
			$nodes[$nodeid]->done = true;
		}
		
		//print_r($newids);
		
	}
	
	
	
	if($depth == $crawldepth) {
		
		renderNetwork();
		
	} else {
		
		$ids = $newids;
		
		$depth++;
		
		makeNetworkFromIds($depth);
	}
}
?>