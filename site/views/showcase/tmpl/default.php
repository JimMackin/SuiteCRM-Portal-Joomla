<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$document = &JFactory::getDocument(); $document->addStyleSheet('components'.DIRECTORY_SEPARATOR.'com_advancedopenportal'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'portal.css');
$document->addScript('components'.DIRECTORY_SEPARATOR.'com_advancedopenportal'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'jquery.2.0.0.min.js');
$document->addScript('components'.DIRECTORY_SEPARATOR.'com_advancedopenportal'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'jquery-ui.1.10.3.min.js');
$document->addScript('components'.DIRECTORY_SEPARATOR.'com_advancedopenportal'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'jquery.prettydate.js');
$document->addScript('components'.DIRECTORY_SEPARATOR.'com_advancedopenportal'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'jquery.form.min.js');
$user =& JFactory::getUser();

$editor =& JFactory::getEditor();
$params = array();
$updateField = $editor->display( 'update_text', '', '', '', '40', '12', false, null, null, null, $params );

function displayNotes($parent){
    if(isset($parent->notes)){
        ?>
        <br><br>
        <hr>
        <span>
                <?php
                echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_FILES');
                ?>
            </span>
        <?php
        foreach($parent->notes as $note){
            if(!$note->id || !$note->filename){
                continue;
            }
            ?>
            <a href="?option=com_advancedopenportal&view=attachment&id=<?php echo $note->id?>" target="_new"><?php echo $note->filename?></a>
        <?php
        }

        ?>
    <?php
    }
}

if($this->allow_case_reopen && strpos($this->case->status, 'Closed') === 0){
    $closeButtonText = JText::_('COM_ADVANCEDOPENPORTAL_REOPEN');
}elseif($this->allow_case_closing && strpos($this->case->status, 'Open') === 0){
    $closeButtonText = JText::_('COM_ADVANCEDOPENPORTAL_CLOSE');
}else{
    $closeButtonText = '';
}

?>

<div id="case">
<h2><?php echo $this->case->name;?> (#<?php echo $this->case->case_number;?>) <span><?php echo JText::_('COM_ADVANCEDOPENPORTAL_RAISED') .' '.$this->case->contact_created_by_name .' '.JText::_('COM_ADVANCEDOPENPORTAL_RAISED_ON').' ' .$this->case->date_entered;?> <?php echo $this->case->status_display;?>
        <?php
        if($closeButtonText) {
            ?>
            <form style='display:inline' action="?option=com_advancedopenportal&task=toggleCaseStatus&format=raw" method="post">
                <input type="hidden" name="case_status" value="<?php echo $this->case->status; ?>">
                <input type="hidden" name="case_id" value="<?php echo $this->case->id; ?>">
                <button type="submit"><?php echo $closeButtonText ?></button>
            </form>
        <?php
        }
        ?>

    </span></h2>
    <div class="case_description">
        <?php
        echo html_entity_decode($this->case->description);
        displayNotes($this->case);
        ?>
    </div>
</div>
<br>
<div id='updates'>
<?php
if(isset($this->case->aop_case_updates)){
    foreach($this->case->aop_case_updates as $update){
    ?>
    <div class='case_update <?php echo $update->poster->type;?>_update'>
        <span><a class="prettyDate" title="<?php echo $update->date_entered;?>"><?php echo $update->date_entered_display;?></a> <strong><?php echo $update->poster->first_name . " " . $update->poster->last_name;?></strong>  <?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_UPDATE_SAID');?>:</span>
        <p><?php echo $update->description;?></p>

        <?php displayNotes($update);?>

    </div>
    <?php
    }
}
?>
</div>
<br><br>
<form class="reply_form" action="?option=com_advancedopenportal&task=addupdate&format=raw" method="post" id="replyForm" name="replyForm" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="case_id" value="<?php echo $this->case->id;?>">
        <input type="file" name="file1" id="file1"><br>
        <a href="javascript:;" id="add_file"><?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_ADD_ANOTHER_FILE');?></a>
        <input type="hidden" name="file_count" id="file_count" value="1"/>
    </div>
    <?php echo $updateField; ?>
    <button class="button" name="send_reply" id='send_reply'><?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_UPDATE_SEND_REPLY');?></button>
</form>
<script>

    function display_update(update){
        var html =  "<div class='case_update contact_update' style='display: none;'><span><a class='prettyDate' title='"+update.date_entered+"'>"+update.date_entered_display+"</a> <strong>"+ update.poster.first_name+" "+ update.poster.last_name+"</strong> <?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_UPDATE_SAID');?>:</span><p>"+update.description+"</p>";
        if(update.notes){
            html = html + "<br><br><hr><span><?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_FILES');?></span>";
            for(var x = 0; x < update.notes.length; x++){
                html = html + '&nbsp;<a href="?option=com_advancedopenportal&view=attachment&id='+update.notes[x].id+'" target="_new">'+update.notes[x].file_name+'</a>';
            }
            html = html + '</div>';
        }
        return html;
    }

    function clear_update(){
        //Remove all extra files
        jQuery('.remove_file').trigger( "click" )
        //Clear file1. Most browsers don't allow setting the value of a file input so we replace #file1 with a copy of itself.
        var fileInput =$('#file1');
        fileInput.val('');
        fileInput.replaceWith( fileInput.clone(true));
        //Clear the text
        jQuery('#update_text').val('');
        if(tinyMCE){
            if(tinyMCE.majorVersion == '4'){
                tinyMCE.get('update_text').setContent('');
            }else{
                tinyMCE.getInstanceById('update_text').setContent('');
            }
        }
        jQuery('#update_text').change();

    }


    jQuery(document).ready(function() {
        jQuery('#add_file').click(function(){
            var new_element = jQuery("#file1").clone();
            var file_count = jQuery("#file_count").val();
            file_count++;
            jQuery("#file_count").val(file_count);
            new_element.attr("id","file"+file_count);
            new_element.attr("name","file"+file_count);
            new_element.val("");

            var div = jQuery('<div>');
            div.insertBefore(jQuery("#add_file"));
            div.append(new_element);
            div.append(jQuery("<button type='button' class='remove_file' id='remove_file"+file_count+"'><?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_REMOVE_FILE');?></button>"));
            return false;
        });
        jQuery('#replyForm').on('click','.remove_file',function(){
            jQuery(this).closest('div').remove()
        });
        jQuery('#replyForm').submit(function(){
            //Perform ajax call to do update.
            if(tinyMCE){
                tinyMCE.triggerSave();
            }
            if(jQuery('#update_text').val() == ''){
                return false;
            }
            //TODO: Clear sending on failure
            var options = {
                target:        '',   // target element(s) to be updated with server response
                beforeSubmit:  function(){
                    jQuery("#send_reply").attr('disabled','disabled');
                    jQuery("#send_reply").text("<?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_UPDATE_SENDING');?>");
                },  // pre-submit callback
                success:       function(responseText, statusText, xhr, form){
                    var data = jQuery.parseJSON(responseText)
                    jQuery('#updates').append(display_update(data));
                    jQuery('.case_update').show('slow');
                    $("a.prettyDate").prettyDate();
                    clear_update();
                    jQuery("#send_reply").text("<?php echo JText::_('COM_ADVANCEDOPENPORTAL_CASE_UPDATE_SEND_REPLY');?>");
                    jQuery("#send_reply").removeAttr('disabled');
                }  // post-submit callback
            };

            $(this).ajaxSubmit(options);

            return false;
        });
        $("a").prettyDate();
        setTimeout(clear_update,1000);
    } );
</script>
