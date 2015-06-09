# SilverStripe Grid Field Extras

## Requirements

* SilverStripe 3.1

## Maintainers

* Guy Watson (guy.watson@internetrix.com.au)

## Description

This module provides a number of useful extensions and extras when working with gridfields

## Usage

#### Dropdown gridfield filtering

The `GridfieldAdvancedFilterHeader` component can add custom fields to a gridfield when filtering. The following example allows the user to filter based on a folder selected from a `TreeDropdownField`

	$grid = new GridField(
		'ExampleGrid',
		'Example Grid',
		$this->Items(),
		GridFieldConfig::create()
			->addComponent(new GridFieldButtonRow('before'))
			->addComponent(new GridFieldToolbarHeader())
			->addComponent($columns = new GridFieldDataColumns())
			->addComponent($filter = new GridFieldAdvancedFilterHeader())
	);

	$columns->setDisplayFields(array(
		'Name' 			=> 'Name',
		'Title'			=> 'Title',
		'Filename'		=> 'Filename'
	));

You can customise what form fields are used on the gridfield to filter specific columns. e.g. 

	$filter->setFilterFields(array(
		'Filename'  => function($record, $column, $grid) { return new TreeDropdownField('Filename', '', 'Folder'); }
	));

The `TreeDropdownField` returns the objects ID. The following method can be used to lookup another field based on the object ID returned by the `TreeDropdownField`

	$filter->setIDToFieldMaps(array(
		'Filename'  => array(
			'Class'			=> 'Folder',
			'LookUpField'	=> 'Filename'
		)
	));

#### Editable Link Columns
Edit linkable objects directly from the gridfield

#### Upload File
The `GridFieldUploadFile` component allows files to be uploaded into a selected folder and then added to the gridfields relation list.
