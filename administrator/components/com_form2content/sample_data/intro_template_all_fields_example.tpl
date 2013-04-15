This template is based on the Smarty template engine. It has been made to display the complete list of Template Parameters for Form2Content version 2.8.0 based on the available field types.
<hr/><h1>{$JOOMLA_TITLE}</h1>
<p>&nbsp;</p>
<table>
<tr>
<td colspan="2"><hr/><strong>General information</strong><hr/></td>
</tr>
<tr>
<td>Title:</td><td>{$JOOMLA_TITLE}</td>
</tr>
<tr>
<td>Title alias:</td><td>{$JOOMLA_TITLE_ALIAS}</td>
</tr>
<tr>
<td>Link to article:</td><td>{$JOOMLA_ARTICLE_LINK}</td>
</tr>
<tr>
<td>Meta keywords:</td><td>{$JOOMLA_META_KEYWORDS}</td>
</tr>
<tr>
<td>Meta description:</td><td>{$JOOMLA_META_DESCRIPTION}</td>
</tr>
<tr>
<td>Joomla Article Id:</td><td>{$JOOMLA_ID}</td>
</tr>
<tr>
<td>Form2Content Form Id:</td><td>{$F2C_ID}</td>
</tr>

<tr>
<td>Section Id:</td><td>{$JOOMLA_SECTION_ID}</td>
</tr>
<tr>
<td>Section title:</td><td>{$JOOMLA_SECTION_TITLE}</td>
</tr>
<tr>
<td>Section alias:</td><td>{$JOOMLA_SECTION_ALIAS}</td>
</tr>
<tr>
<td>Category Id:</td><td>{$JOOMLA_CATEGORY_ID}</td>
</tr>
<tr>
<td>Category title:</td><td>{$JOOMLA_CATEGORY_TITLE}</td>
</tr>
<tr>
<td>Category alias:</td><td>{$JOOMLA_CATEGORY_ALIAS}</td>
</tr>
<tr>
<td>Created:</td><td>{$JOOMLA_CREATED}</td>
</tr>
<tr>
<tr>
<td>Modified:</td><td>{$JOOMLA_MODIFIED}</td>
</tr>
<tr>
<tr>
<td>Start publishing:</td><td>{$JOOMLA_PUBLISH_UP}</td>
</tr>
<tr>
<td>Finish publishing:</td><td>{$JOOMLA_PUBLISH_DOWN}</td>
</tr>
<tr>
<td>Author:</td><td>{$JOOMLA_AUTHOR}</td>
</tr>
<tr>
<td>Author username:</td><td>{$JOOMLA_AUTHOR_USERNAME}</td>
</tr>
<tr>
<td>Author e-mail:</td><td>{$JOOMLA_AUTHOR_EMAIL}</td>
</tr>
<tr>
<td>Author Id:</td><td>{$JOOMLA_AUTHOR_ID}</td>
</tr>
<tr>
<td>Author Alias:</td><td>{$JOOMLA_AUTHOR_ALIAS}</td>
</tr>
<tr>
<td colspan=\"2\"><hr/><strong>Images information</strong><hr/></td>
</tr>
<tr>
<td>Images path absolute:</td><td>{$F2C_IMAGES_PATH_ABSOLUTE}</td>
</tr>
<tr>
<td>Images path relative:</td><td>{$F2C_IMAGES_PATH_RELATIVE}</td>
</tr>
<tr>
<td>Thumbs path absolute:</td><td>{$F2C_IMAGES_PATH_THUMBS_ABSOLUTE}</td>
</tr>
<tr>
<td>Thumbs path relative:</td><td>{$F2C_IMAGES_PATH_THUMBS_RELATIVE}</td>
</tr>
<tr>
<td colspan="2"><hr/>
<strong>Custom Fields information</strong>
<hr/></td>
</tr>
<tr>
<td colspan="2"><p>The names used in the exampes below are <strong>CUSTOM</strong> and are set by the administrator creating the form.</p></td>
</tr>
<tr>
<td>checkbox:</td><td>{$CHECKBOX}</td>
</tr>
<tr>
<td>database (value):</td><td>{$DATABASE}</td>
</tr>
<tr>
<td>database (text):</td><td>{$DATABASE_TEXT}</td>
</tr>
<tr>
<td>date:</td><td>{$DATE}</td>
</tr>
<tr>
<td>displaylist:</td><td>{$DISPLAYLIST}</td>
</tr>
<tr>
<td>email:</td><td>{$EMAIL}</td>
</tr>
<tr>
<td>file (url):</td><td>{$FILE}</td>
</tr>
<tr>
<td>file (filename):</td><td>{$FILE_FILENAME}</td>
</tr>
<tr>
<td>geocoder (addres):</td><td>{$GEOCODER_ADDRESS}</td>
</tr>
<tr>
<td>geocoder (latitude):</td><td>{$GEOCODER_LAT}</td>
</tr>
<tr>
<td>geocoder (longitude):</td><td>{$GEOCODER_LON}</td>
</tr>
<tr>
<td>hyperlink:</td><td>{$HYPERLINK}</td>
</tr>
<tr>
<td>iframe:</td><td>{$IFRAME}</td>
</tr>
<tr>
<td>image:</td><td>{$IMAGE}</td>
</tr>
<tr>
<td>info:</td><td>{$INFO}</td>
</tr>
<tr>
<td>multiselectlist:</td><td><ul>{$MULTISELECTLIST}</ul></td>
</tr>
<tr>
<td>editor:</td><td>{$EDITOR}</td>
</tr>
<tr>
<td>textarea:</td><td>{$TEXTAREA}</td>
</tr>
<tr>
<td>textbox:</td><td>{$TEXTBOX}</td>
</tr>
<tr>
<td>singleselect (value):</td><td>{$SINGLESELECT}</td>
</tr>
<tr>
<td>singleselect (text):</td><td>{$SINGLESELECT_TEXT}</td>
</tr>
</table>
