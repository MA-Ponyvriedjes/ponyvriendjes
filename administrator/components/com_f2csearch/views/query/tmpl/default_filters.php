<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.calendar');
?>

<script language="javascript" type="text/javascript">
   Joomla.submitbutton = function (pressbutton) {
    if (pressbutton == 'save' || pressbutton == 'apply') {
      
      if (document.adminForm.title.value == '') {
        alert("Please enter the name of the query.");
      }
      else{
        submitform(pressbutton);
      }
    } else {
      submitform(pressbutton);
    }
  }
</script>

<form action="index.php" method="post" name="adminForm"
id="adminForm">
  <fieldset class="adminform">
  <legend>Details</legend>
  <table class="admintable">
    <tr>
      <td width="100" align="right" class="key">Published:</td>
      <td>
        <?php echo JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->query->published); ?>
      </td>
    </tr>
    <tr>
      <td width="100" align="right" class="key">Title:</td>
      <td>
        <input class="text_area" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->query->title;?>" />
      </td>
    </tr>
    
    <tr>
      <td width="100" align="right" class="key">Show Articles from Published date:</td>
      <td>
        <?php echo JHTML::_('calendar', $this->query->fromdate, 'fromdate', 'fromdate'); ?>
      </td>
    </tr>
    <tr>
      <td width="100" align="right" class="key">Show Articles till Published date:</td>
      <td>
         <?php echo JHTML::_('calendar', $this->query->tilldate, 'tilldate', 'tilldate'); ?>
      </td>
    </tr>
  </table>

  <div id="f2csearch-general-filter">   
          <h3>Choose one or more F2C Content Types</h3>
          <span>Leave blank to include all</span> 
          <ul>
           
            <li>
              <?php echo JHTML::_('select.genericlist',  $this->optionLists['f2cprojectList'], 'form_id[]', 'class="inputbox" multiple="multiple" size="8"', 'id', 'title', explode(',',$this->query->form_id)); ?>
            
            </li>
           
          </ul>
     </div>
    <div id="filters" style="float:left;width:100%;">
    <h3> Filters</h3>
    <p> The following statements all have to be true before an article will be included in the selection</p>
   
      <table class="adminlist required">
      <thead>
        <tr>
          <th width="10%"><input type="checkbox" name="toggle" 
               value="" onclick="checkAll(<?php echo 
               count( $this->filters ); ?>);" /></th>
          <th>Type</th>
          <th>Veld</th>
          <th>Operator</th>
          <th>Automenu</th>

        </tr>
      </thead>
      <tbody>
          <?php 
            $i=1; 
            foreach($this->filters as $filter) :
          ?>
                <tr class="row<?php echo ($i-floor($i / 2)*2) . ' ' . $this->filter->type; ?>">
                    <?php echo F2csearchHelper::renderFilter($filter,$filter->type,$i); ?>
                </tr>
          <?php
                $i++;
            
            endforeach;
          ?>
            <tr>
                <td ><?php echo JHTML::_('select.genericList', $this->optionLists['subquerytypes'], 'helper', 'class="inputbox" '. '', 'value', 'text', 'f2cformfield' ); ?><a href="#" class="newfilter_btn">Filter toevoegen</a></td>
            </tr>
      </tbody>
      </table>
   
     </div>
     <div id="ordering">
        <?php echo JHTML::_('select.genericList', $this->optionLists['orderingtypes'], 'helper', 'class="inputbox" '. '', 'value', 'text', 'ordering[0]' ); ?>
        <?php echo JHTML::_('select.genericList', $this->optionLists['orderingtypes'], 'helper', 'class="inputbox" '. '', 'value', 'text', 'ordering[1]' ); ?>
        <?php echo JHTML::_('select.genericList', $this->optionLists['orderingtypes'], 'helper', 'class="inputbox" '. '', 'value', 'text', 'ordering[2]' ); ?>
        
     </div>>
        
       
   
  <input type="hidden" name="query_id"
  value="<?php echo $this->query->query_id; ?>" />
  <input type="hidden" name="option"
  value="com_f2csearch" />
  <input type="hidden" name="task"  value="save" />
  <?php echo JHTML::_( 'form.token' ); ?>
  </fieldset>
</form>

<div id="lib" style="display:none">
  <table>
  <tbody>
    <tr class="subquery" >  
              <td> <input class="subquery_id" type="hidden" name="subquery[<ID>][subquery_id]" value="" /> </td>
              <td> <input class="query_id" type="hidden" name="subquery[<ID>][query_id]" value="<?php echo $this->query->query_id; ?>" /> </td>
              <td> <input class="type" type="hidden" name="subquery[<ID>][type]" value="" /> </td>
              <td> <input class="required" type="hidden" name="subquery[<ID>][required]" value="" /> </td>

    </tr>
    <tr class="filter f2c-formfield" >  
              <?php echo F2csearchHelper::renderFilter(NULL,'f2c-formfield','<ID>'); ?>
                   
    </tr>
    <tr class="filter user-profile" > 
              <?php echo F2csearchHelper::renderFilter(NULL,'user-profile','<ID>'); ?>
     </tr>
    <tr class="filter user-rating" >  
            <?php echo F2csearchHelper::renderFilter(NULL,'user-rating','<ID>'); ?>

    </tr>
     <tr class="filter f2c-author" >  
            <?php echo F2csearchHelper::renderFilter(NULL,'f2c-author','<ID>'); ?>

    </tr>
     <tr class="filter category" >  
            <?php echo F2csearchHelper::renderFilter(NULL,'category','<ID>'); ?>

    </tr>
  </tbody>
  </table>
</div>
