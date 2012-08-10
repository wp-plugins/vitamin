<?php

class redirsEditForm{
  function __construct($data, $form_submit_path, $key){

?>
<div id="dashboard-widgets-wrap">
  <div id="dashboard-widgets" class="metabox-holder">
    <div class="postbox-container" style="width:50%;"><div class="meta-box-sortables ui-sortable" style="min-height: 50px;">

      <form action="<?php echo $form_submit_path; ?>&amp;keyToUpdate=<?php echo $key; ?>" method="post">

        <div class="postbox">
          <h3 style="cursor:text"><?php echo empty($key) ? 'Add' : 'Replace'; ?> Redirection</h3>
          <div class="inside">

            <table style="width:100%">
                <tr>
                    <td><label for="orgUrl">Orginal URL:</label></td>
                    <td><input type="text" id="orgUrl" name="orgUrl" value="<?php echo esc_attr( $data->orgUrl ); ?>" style="width:100%" /></td>
                </tr>
                <tr>
                    <td><label for="redirUrl">Redirect to:</label></td>
                    <td><input type="text" id="redirUrl" name="redirUrl" style="width:100%" value="<?php echo esc_attr( $data->redirUrl ); ?>" style="width:100%" /></td>
                </tr>
                <tr>
                    <td><label for="priority">Priority:</label></td>
                    <td>
                        <select id="priority" name="priority">
                          <option value=1>Priority 1 - by htaccess if possible</option>
                          <?php
                            for ($i=2;$i<=10;$i++) {
                                echo '<option value="'.$i.'"';
                                if($i==$data->priority) echo ' selected="selected"';
                                echo '>Priority '.$i.'</option>'."\n";

                            }
                          ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="code">Redirect code:</label></td>
                    <td>
                        <select id="code" name="code">
                          <option value="301">301 Moved Permanently</option>
                          <option value="302"<?php if(302==$data->code) echo ' selected="selected"'; ?>>302 Found (originally temporary redirect) &nbsp; </option>
                          <option value="307"<?php if(307==$data->code) echo ' selected="selected"'; ?>>307 Temporary Redirect</option>
                        </select>
                        <input type="hidden" name="add_redir" value="true" />
                    </td>
                </tr>
            </table>
            <p style="text-align:right">
                    <input type="submit" value="<?php echo empty($key) ? 'Add' : 'Replace';
                    ?>" title="<?php echo empty($key) ? 'Add' : 'Replace';
                    ?>" class="button-primary" />
            </p>
            <div class="clear"></div>
          </div>
        </div>
      </form>

    </div>
  </div>

  <div id="postbox-container-2" class="postbox-container" style="width:50%;">
    <div class="meta-box-sortables ui-sortable" style="min-height: 50px;">

      <div class="postbox ">
        <h3 style="cursor:text">Orginal URL</h3>
        <div class="inside">
          <p>
            URL path, which will be redirected.
          </p>
          <p>
            You may use expression <code>%anything%</code> in this field.
          </p>
          <p>
            Example: <code>/page-with-changed-slug/</code>
          </p>
          <div class="clear"></div>
        </div>
      </div>

      <div class="postbox ">
        <h3 style="cursor:text">Redirect to</h3>
        <div class="inside">
          <p>
            URL Adress, where user will be redirected. "Redirect to" and "Orginal URL" field should not be same!
          </p>
          <p>
            You may use expression <code>%anything%</code> in this field.
          </p>
          </p>
            For example you may redirect "Orginal URL": <code>%anything%/nofollow</code>
            to "Redirect to": <code>%anything%/</code>
          <p>
          <div class="clear"></div>
        </div>
      </div>

      <div class="postbox ">
        <h3 style="cursor:text">Priority</h3>
        <div class="inside">
          <p>
            The hightest prority is 1, the lowest is 10.
          </p>
          <p>
            If there are 2 possible interpretation of redirecting, rule with lower number in priority will be applied.
          </p>
          <p>
            If there are 2 possible interpretation of redirecting with same priority, Aplied rule will be one random of them.
          </p>
          <p>
            Option <strong>Priority 1</strong> will be applied by .htaccess if possible. This priority should be used in anti RFI hackers attack rules.
            By this option you may disable redirection statistics for URL.
          </p>
          <div class="clear"></div>
        </div>
      </div>

      <div class="postbox ">
        <h3 style="cursor:text">Code</h3>
        <div class="inside">
          <p>
            Code means google type of redirection.
          </p>
          <p>
            Default value for this field is 301 and that is the best way how to redirect.
            However 302 and 307 codes may be used by advanced users.
          </p>
          <div class="clear"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
  }
}
