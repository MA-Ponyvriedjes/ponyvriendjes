//f2c query fields only

SELECT jos_content.* , digirating.user_rating
FROM  jos_f2c_form , jos_content
LEFT JOIN ( 
	SELECT * 
	FROM jos_userrating
	WHERE user_id=42
) as digirating ON jos_content.id = digirating.album_id 
WHERE jos_f2c_form.reference_id=jos_content.id 
AND (
	jos_f2c_form.id IN (
		SELECT field1.formid 
		FROM (	
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (10) 
			AND content='album' 
		) as field1 
		INNER JOIN (	
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (13) 
			AND content='pope' 
		) as field2 
		ON field1.formid = field2.formid 
	) 
	OR 
	jos_f2c_form.id IN (
		SELECT field3.formid FROM ( 
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (13) AND content='rock' 
		) as field3 
	) 
	OR 
	jos_f2c_form.id IN (
		SELECT field4.formid 
		FROM (	
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (35,36,37,38) 
			AND content='3 J\'s' 
		) 
		as field4 ) 
)

//user profile query

SELECT jos_content.* 
FROM jos_content, jos_f2c_form 
WHERE jos_f2c_form.reference_id=jos_content.id 
AND (
	jos_f2c_form.id IN (

		SELECT filter1.formid FROM (	
		           SELECT DISTINCT formid 
			       FROM jos_f2c_fieldcontent, jos_user_profiles 
		           WHERE fieldid IN (13) 
		           AND user_id = 46 
		           AND profile_key = profile.nederlandstalig
		           AND profile_value = content
		      )
		      AS filter1
	)

	AND 

	((
		SELECT filter1.formid FROM (	
		           SELECT DISTINCT formid 
			       FROM jos_f2c_fieldcontent, jos_user_profiles 
		           WHERE fieldid IN (13) 
		           AND user_id = 46 
		           AND profile_key = profile.nederlandstalig
		           AND profile_value = content
		      )
		      AS filter1
	)
	)

	A filter says => A certain F2C field -OPERATOR- A value
	A subquery is a combination of those filters => Filter1 AND filter2 AND filter3
	Those subquerys are then arranged  
    /*INCLUDE ONLY IF             GATEWAY NOT A KILLER */
	(.. AND .. AND .. AND) AND (.. OR .. OR .. OR..)




	SELECT field1.formid 
		FROM (	
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (10) 
			AND content='album' 
		) as field1 
		INNER JOIN (	
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (13) 
			AND content='pope' 
		) as field2 
		ON field1.formid = field2.formid 
	
		SELECT field3.formid FROM ( 
			SELECT DISTINCT formid 
			FROM jos_f2c_fieldcontent 
			WHERE fieldid IN (13) AND content='rock' 
		) as field3 
	
	
		SELECT filter1.formid FROM (	
		           SELECT DISTINCT formid 
			       FROM jos_f2c_fieldcontent, jos_user_profiles 
		           WHERE fieldid IN (13) AND content = profile_value
		           AND user_id = 46 AND profile_key = 'profile.nederlandstalig'
		           
		           SELECT DISTINCT formid 
		           FROM jos_f2c_fieldcontent, jos_user_profiles 
		           WHERE fieldid IN (13) AND content = 'nederlandstalig' 
		           AND user_id = 42 AND profile_key = 'nederlandstalig'
		      )
		      AS filter1

		getItems
getMenuItems
setParams

getItemsQuery
getMenuItemsQuery

		MenuIItemsQuery


		SELECT DISTINCT jos_f2c_fieldcontent.content as value, jos_f2c_fieldcontent.content as title 
						jos_f2c_form.created_by as value, jos_users.name as title 
						jos_f2c_form.catid as value, jos_categories.title as title				
		
		FROM jos_f2c_form, jos_f2c_fieldcontent
							jos_users
							jos_categories
		
		WHERE jos_f2c_form.access IN (" . $groups . ")
		AND jos_f2c_form.state = 1
		AND (jos_f2c_form.publish_up = " . $nullDate . " OR jos_f2c_form.publish_up <= " . $nowDate . ")
		AND (jos_f2c_form.publish_down = " . $nullDate . " OR jos_f2c_form.publish_down >= " . $nowDate . ") ";
		
		
		AND " . $this->getChainedParameters() . " 
		AND	" . $this->getChainedSubquerys($this->requiredSubquerys) . " 
		AND (" . $this->getChainedSubquerys($this->optionalSubquerys) .") ";