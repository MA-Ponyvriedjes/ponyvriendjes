<?phpdefined( '_JEXEC' ) or die( 'Restricted access' ); ?><form action="index.php" method="post" name="adminForm"><table class="adminlist">  <thead>    <tr>      <th width="20">        <input type="checkbox" name="toggle"              value="" onclick="checkAll(<?php echo              count( $this->rows ); ?>);" />      </th>      <th width="15">      </th>      <th class="title">Name</th>           <th width="5%" nowrap="nowrap">Published</th>    </tr>  </thead>  <?php  jimport('joomla.filter.output');  $k = 0;  for ($i=0, $n=count( $this->rows ); $i < $n; $i++)   {    $row = &$this->rows[$i];    $checked = JHTML::_('grid.id', $i, $row->query_id );    $published = JHTML::_('grid.published', $row, $i );	$link = JFilterOutput::ampReplace( 'index.php?option=' . $this->option . '&task=edit&cid[]='. $row->query_id );    ?>    <tr class="<?php echo "row$k"; ?>">      <td>        <?php echo $checked; ?>      </td>      <td>        <?php echo $row->query_id; ?>      </td>      <td>        <a href="<?php echo $link; ?>"><?php echo $row->title; ?></a>      </td>       <td align="center">        <?php echo $published;?>      </td>    </tr>    <?php    $k = 1 - $k;  }  ?></table><?php echo JHTML::_( 'form.token' ); ?><input type="hidden" name="option" value="com_f2csearch" /><input type="hidden" name="task" value="" /><input type="hidden" name="boxchecked" value="0" /><input type="hidden" name="controller" value="query" /></form>