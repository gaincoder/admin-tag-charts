<?php

class pluginAdminTagCharts extends Plugin {


	public function adminSidebar()
	{
		$html = '<a id="current-version" class="nav-link" href="'.HTML_PATH_ADMIN_ROOT.'plugin/pluginAdminTagCharts">Tag Charts</a>';
		return $html;
	}


    public function adminView(){
        global $tags;
        global $L;

        $html = '<h2 class="m-0">Tag Charts</h2>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th class="border-bottom-0" scope="col">Name</th>
                            <th class="border-bottom-0" scope="col">URL</th>
                            <th class="border-bottom-0" scope="col">'.$L->get('Usage').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        array_walk($tags->db,function(&$tag,$key){
           $tag['usage'] = count($tag['list']);
           $tag['key'] = $key;
        });

        usort($tags->db,function($tagA,$tagB){
            $a = $tagA['usage'];
            $b = $tagB['usage'];
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? 1 : -1;
        });

        foreach( $tags->db as $fields ) {
            $html .= '<tr>';
            $html .= '<td>'.$fields['name'].'</td>';
            $html .= '<td><a target="_blank" href="'.DOMAIN_TAGS.$fields['key'].'">'.'/tag/'.$fields['key'].'</a></td>';
            $html .= '<td>'.$fields['usage'].'</td>';
            $html .= '</tr>';
        }


        $html .= '</tbody></table>';
        return $html;
    }
}
