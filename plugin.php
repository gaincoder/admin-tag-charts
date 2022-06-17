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
        if ($_POST['type']==='delete') {
            if ($this->deleteTag($_POST['key'])) {
                Alert::set( $L->g('Tag deleted') );
            }
        }
        $html = '<h2 class="m-0">Tag Charts</h2>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th class="border-bottom-0" scope="col">Name</th>
                            <th class="border-bottom-0" scope="col">URL</th>
                            <th class="border-bottom-0" scope="col">'.$L->get('Usage').'</th>
                            <th class="border-bottom-0" scope="col"></th>
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
            $html .= '<td><a href="#" class="text-danger deleteTagButton" data-toggle="modal" data-target="#jsdeleteTagModal" data-key="'.$fields['key'].'"><i class="fa fa-trash"></i>'.$L->g('Delete').'</a></td>';
            $html .= '</tr>';
        }


        $html .= '</tbody></table>';
        echo Bootstrap::modal(array(
            'buttonPrimary'=>$L->g('Delete'),
            'buttonPrimaryClass'=>'btn-danger deleteTagModalAcceptButton',
            'buttonSecondary'=>$L->g('Cancel'),
            'buttonSecondaryClass'=>'btn-link',
            'modalTitle'=>$L->g('Delete tag'),
            'modalText'=>$L->g('Are you sure you want to delete this tag?'),
            'modalId'=>'jsdeleteTagModal'
        ));
        echo <<<SCRIPT
<script type="text/javascript">
$(document).ready(function() {
    
	var key = false;

	// Button for delete a page in the table
	$(".deleteTagButton").on("click", function() {
		key = $(this).data('key');
	});    
    
    $(".deleteTagModalAcceptButton").on("click", function() {
            var form = jQuery('<form>', {
                'action': HTML_PATH_ADMIN_ROOT+'plugin/pluginAdminTagCharts',
                'method': 'post',
                'target': '_top'
		}).append(jQuery('<input>', {
			'type': 'hidden',
			'name': 'tokenCSRF',
			'value': tokenCSRF
		}).append(jQuery('<input>', {
			'type': 'hidden',
			'name': 'key',
			'value': key
		}).append(jQuery('<input>', {
			'type': 'hidden',
			'name': 'type',
			'value': 'delete'
		}))));

    
            form.hide().appendTo("body").submit();
        });
});
</script>
SCRIPT;



        return $html;
    }

    private function deleteTag($key){
        global $tags;
        global $pages;

        $tags->reindex();
        $tagName = $tags->getName($key);
        $list = $tags->getList($key,1,-1);
        foreach ($list as $pageKey) {
                $page = new Page($pageKey);

                $pageTags = $page->tags(true);
                $newTags = array();
                foreach ($pageTags as $pageTag){
                    if($pageTag !== $tagName){
                        $newTags[] = $pageTag;
                    }
                }

                $pages->edit(['key'=>$pageKey,'tags'=>implode(',',$newTags)]);
        }

        $tags->remove($key);
        return true;
    }
}
