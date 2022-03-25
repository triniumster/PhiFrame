<?php

/**
 * @author Triniumster@System.Hardcore.PL
 * @license X11 (2018) Use as is without any gwaranty 
 * 
 */

class FileUpload {
    
    public static function getButton(string $title = 'Select File', string $subAction = 'default', int $maxSize = 1048576, bool $required = true, bool $multiple = false, int $maxFiles = 1){
        $upload = new html('form');
        $upload->input("*button; =$title")->onclick("$('#uploadfile_$subAction').click()");
        $btn = $upload->input("#uploadfile_$subAction; *file")->display('none')->onchange("FileUpload.upload($(this), event)")->attr('sub-action', $subAction)->attr('max-size', $maxSize)->attr('max-count', $maxFiles);

        if($required)
            $btn->required();
        
        if($multiple)
            $btn->multiple();
        
        return $upload;
    }
    
    public static function script($cmd = null){
        if($cmd == null)
            $cmd = fstr('cmd', INPUT_GET);
    ?>
        <script>

            <?php echo "FileUpload = {cmd: '$cmd'};"; ?>
           
            FileUpload.upload = function(button, event){
                
                var maxSize = button.attr('max-size');
                var maxFiles = button.attr('max-count');
                var subAction = button.attr('sub-action');
                var files = event.target.files;
                var data = new FormData();
                
                if(maxFiles !== '0' && files.length > maxFiles){
                    showDialogOK('Wybrano zbyt wiele plików. Maksymalnie '+maxFiles);
                    return;
                }
    
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    
                    if(maxSize !== '0' && file.size > maxSize){
                        showDialogOK(file.name+' jest zbyt duży ('+(file.size/1024)+' kiB). Maksymalnie '+(maxSize/1024)+' kiB.');
                        return;
                    }
                    
                    data.append('file', file, file.name);
                }

                data.append('ajax', true);
                data.append('action', 'PhiFrameFileUpload');
                data.append('subaction', subAction);

                $.ajax({
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        xhr.timeout = 0;
                        
                        if(xhr.upload){
                            xhr.upload.addEventListener("progress", function(evt){
                                if (evt.lengthComputable) {
                                    var percentComplete = Math.ceil(100 * (evt.loaded || evt.position) / evt.total);
                                    FileUpload.uploadProcessing(percentComplete, subAction);
                                }

                                if (percentComplete === 100)
                                    FileUpload.uploadDone(subAction);
                            }, false);
                        }
                    
                        FileUpload.uploadBegin(subAction);
                        return xhr;
                    },
                    timeout: 1200000,
                    url: "index.php?cmd="+FileUpload.cmd,
                    type: "POST",
                    data: data,
                    success: function(result) {
                        if(typeof result !== 'object' || (result.hasOwnProperty('error') && result.error)){
                            if(typeof FileUpload.onFailed === "function")
                                FileUpload.onFailed(1, result, result.error);
                            else if(typeof result !== 'object')
                                Ajax.showDialog('', result);
                            else
                                Ajax.showDialog('', result.error);

                            return;
                        }

                        if(result.hasOwnProperty('message')){
                            alert(result.message);
                            return;
                        }
                        
                        if (typeof FileUpload.onSuccess === "function")
                            FileUpload.onSuccess(result, subAction);
                    },
                    error: function(xhr, textStatus, thrownError) {
                        if(typeof FileUpload.onFailed === "function")
                            FileUpload.onFailed(0, xhr, textStatus);
                        else
                            Ajax.showDialog(textStatus, xhr.responseText+' '+xhr.status+' '+thrownError);
                    },
                    async: true,
                    cache: false,
                    contentType: false,
                    processData: false
                });
            };
            
            FileUpload.uploadBegin = function(subaction){};
            FileUpload.uploadProcessing = function(percent, subaction){};
            FileUpload.uploadDone = function(subaction){};
            FileUpload.onSuccess = null; //function(result, subaction);
            FileUpload.onFailed = null; //function(errorid, jqXHR, textStatus);
        </script>
    <?php }
    
    public static function ajaxServe($callback){
        if(fstr('action') == 'PhiFrameFileUpload'){
            $callback(fstr_null('subaction'), $_FILES["file"]);  
            done();
        }
    }
}