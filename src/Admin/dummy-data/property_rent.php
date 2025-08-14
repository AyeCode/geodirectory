<?php
/**
 * GD Property for rent dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/'; // CDN URL will be faster

// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['apartments'] = array(
	'name'        => 'Apartments',
	'icon'        => $dummy_image_url . 'cat_icon/Apartments.png',
	'schema_type' => 'Residence',
	'font_icon'   => 'fas fa-building',
	'color'       => '#254e4e',
);
$dummy_categories['houses'] = array(
	'name'        => 'Houses',
	'icon'        => $dummy_image_url . 'cat_icon/Houses.png',
	'schema_type' => 'Residence',
	'font_icon'   => 'fas fa-home',
	'color'       => '#5551b9',
);
$dummy_categories['commercial'] = array(
	'name'        => 'Commercial',
	'icon'        => $dummy_image_url . 'cat_icon/Commercial.png',
	'font_icon'   => 'fas fa-industry',
	'color'       => '#852d2d',
);
$dummy_categories['land'] = array(
	'name'        => 'Land',
	'icon'        => $dummy_image_url . 'cat_icon/Land.png',
	'font_icon'   => 'fas fa-map',
	'color'       => '#84612d',
);

// Set any custom fields
$dummy_custom_fields = GeoDir_Admin_Dummy_Data::extra_custom_fields($post_type); // set extra default fields

// Set any sort fields
$dummy_sort_fields = array();

// date added
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => '',
	'field_type' => 'datetime',
	'frontend_title' => __('Newest','geodirectory'),
	'htmlvar_name' => 'post_date',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '1',
);

// title
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'text',
	'frontend_title' => __('Title','geodirectory'),
	'htmlvar_name' => 'post_title',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '0',
);
// price
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'FLOAT',
	'field_type' => 'text',
	'frontend_title' => __('Price','geodirectory'),
	'htmlvar_name' => 'price',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '0',
);
// rating
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'float',
	'frontend_title' => __('Rating','geodirectory'),
	'htmlvar_name' => 'overall_rating',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '0',
);
// Bedrooms
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'select',
	'frontend_title' => __('Bedrooms','geodirectory'),
	'htmlvar_name' => 'property_bedrooms',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '0',
);

// Set dummy posts
$dummy_posts = array();
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Eastern Lodge',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec non augue ultrices, vulputate nulla at, consectetur ante. Quisque neque mi, vulputate quis nulla a, sollicitudin fringilla leo. Nam dictum id neque eu imperdiet. Curabitur ligula turpis, malesuada at lobortis commodo, vulputate volutpat arcu. Duis bibendum blandit aliquam. In ipsum diam, tristique ut bibendum vel, lobortis non tellus. Nulla ultricies, ante vitae placerat auctor, nisi quam blandit enim, sit amet aliquam est diam id urna. Suspendisse eget nibh volutpat, malesuada enim sed, egestas massa.

	Aliquam ut odio ullamcorper, posuere enim sed, venenatis tortor. Donec justo elit, aliquam sed cursus sed, semper eget libero. Mauris consequat lorem sed fringilla tincidunt. Phasellus suscipit velit et elit tristique, ac commodo metus scelerisque. Vivamus finibus ipsum placerat pulvinar aliquet. Maecenas augue orci, blandit at nibh pharetra, condimentum congue ligula. Duis non ante sagittis odio convallis lacinia in quis sapien.

	Curabitur molestie vel ipsum non eleifend. Pellentesque eu nulla sed magna condimentum finibus. Aliquam vel ullamcorper eros, eget lacinia eros. Nam tempor auctor tortor, eget tempor dui rhoncus in. Donec posuere sit amet odio eget pharetra. Duis nec tortor id urna dignissim bibendum. Phasellus eu leo consectetur, tincidunt ipsum sed, aliquet felis. Praesent eu consequat mauris, ac pulvinar velit. Curabitur vel purus in mauris elementum bibendum sit amet a erat. Suspendisse suscipit nec libero at pellentesque.

	Vestibulum tristique quam eget bibendum pulvinar. Mauris sit amet magna ut arcu rutrum pellentesque feugiat et ipsum. Proin porta quam sed risus accumsan pharetra. Nulla quis semper nisl. Nulla facilisi. Nulla facilisi. Pellentesque euismod sollicitudin lacus vel ultricies. Vestibulum ut sem ut nulla ultricies convallis in at mi. Nunc vitae nibh arcu. Maecenas nunc enim, tempus a rhoncus eget, pellentesque ut erat.

	Suspendisse interdum accumsan magna et tempor. Suspendisse scelerisque at lorem sit amet faucibus. Aenean quis consectetur enim. Duis aliquet tristique tempus. Suspendisse id ullamcorper mauris. Aliquam in libero eu justo porttitor pulvinar. Nulla semper placerat lectus. Nulla mollis suscipit lacus, a blandit purus cursus non. Maecenas id tellus mi. Pellentesque sollicitudin nibh eget magna scelerisque consequat. Aliquam convallis orci arcu, et euismod dui cursus et. Donec nec pellentesque nulla, ac pretium massa. In gravida bibendum ornare.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'Property', 'Real Estate' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(111) 677-4444',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '1750',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Furnished',
	'property_type' => 'Detached house',
	'property_bedrooms' => '3',
	'property_bathrooms' => '2',
	'property_area' => '1850',
	'property_features' => 'Gas Central Heating,Triple Glazing,Front Garden,Private driveway,Fireplace',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Daisy Street',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf2.jpg",
		"$dummy_image_url/ps/psl2.jpg",
		"$dummy_image_url/ps/psb2.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'Garage' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '1150',
	"property_status" => 'Let',
	'property_furnishing' => 'Unfurnished',
	'property_type' => 'Detached house',
	'property_bedrooms' => '5',
	'property_bathrooms' => '3',
	'property_area' => '2650',
	'property_features' => 'Oil Central Heating,Front Garden,Garage,Private driveway,Fireplace',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Northbay House',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf3.jpg",
		"$dummy_image_url/ps/psl3.jpg",
		"$dummy_image_url/ps/psb3.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'House', 'Property' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '1300',
	"property_status" => 'Under Offer',
	'property_furnishing' => 'Unfurnished',
	'property_type' => 'Detached house',
	'property_bedrooms' => '6',
	'property_bathrooms' => '6',
	'property_area' => '1650',
	'property_features' => 'Gas Central Heating,Triple Glazing,Off Road Parking,Fireplace',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Jesmond Mansion',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf4.jpg",
		"$dummy_image_url/ps/psl4.jpg",
		"$dummy_image_url/ps/psb4.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'House', 'Real Estate' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '13000',
	"property_status" => 'Under Offer',
	'property_furnishing' => 'Partially furnished',
	'property_type' => 'Detached house',
	'property_bedrooms' => '10',
	'property_bathrooms' => '7',
	'property_area' => '6600',
	'property_features' => 'Oil Central Heating,Double Glazing,Front Garden,Garage,Private driveway,Fireplace',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Springfield Lodge',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf5.jpg",
		"$dummy_image_url/ps/psl5.jpg",
		"$dummy_image_url/ps/psb5.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'House', 'Logde' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '1800',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Optional',
	'property_type' => 'Detached house',
	'property_bedrooms' => '4',
	'property_bathrooms' => '3',
	'property_area' => '3700',
	'property_features' => 'Oil Central Heating,Double Glazing,Front Garden',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Forrest Park',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf6.jpg",
		"$dummy_image_url/ps/psl6.jpg",
		"$dummy_image_url/ps/psb6.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Houses' ),
	"post_tags" => array( 'Park' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '2700',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Unfurnished',
	'property_type' => 'Detached house',
	'property_bedrooms' => '5',
	'property_bathrooms' => '4',
	'property_area' => '2250',
	'property_features' => 'Gas Central Heating,Double Glazing,Front Garden,Private driveway',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Fraser Suites',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf7.jpg",
		"$dummy_image_url/ps/psl4.jpg",
		"$dummy_image_url/ps/psb4.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Apartments' ),
	"post_tags" => array( 'Property', 'Suites' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '1450',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Unfurnished',
	'property_type' => 'Apartment',
	'property_bedrooms' => '3',
	'property_bathrooms' => '2',
	'property_area' => '1250',
	'property_features' => 'Gas Central Heating,Double Glazing',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Richmore Apartments',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf8.jpg",
		"$dummy_image_url/ps/psl2.jpg",
		"$dummy_image_url/ps/psb3.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Apartments' ),
	"post_tags" => array( 'Property', 'Real Estate' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '2000',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Unfurnished',
	'property_type' => 'Apartment',
	'property_bedrooms' => '2',
	'property_bathrooms' => '2',
	'property_area' => '1750',
	'property_features' => 'Gas Central Heating,Double Glazing,Garage',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Hotel Alpina',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf9.jpg",
		"$dummy_image_url/ps/psl2.jpg",
		"$dummy_image_url/ps/psb5.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"post_category" => array( 'Commercial' ),
	"post_tags" => array( 'Five Star' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '60000',
	"property_status" => 'For Rent',
	'property_furnishing' => 'Furnished',
	'property_type' => 'Hotel',
	'property_bedrooms' => '120',
	'property_bathrooms' => '133',
	'property_area' => '35000',
	'property_features' => 'Gas Central Heating,Double Glazing,Garage',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_title" => 'Development Land',
	"post_content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ipsum congue enim elementum ornare. Vestibulum id ipsum ac massa malesuada rutrum. Curabitur id erat nec mauris hendrerit pretium. Aliquam pretium sollicitudin enim ac hendrerit. Phasellus et enim elit. Mauris ac maximus enim. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Ut ut cursus leo. Aenean lacinia risus ut ex sodales, a dictum eros vulputate. Sed ornare ex eget velit fringilla luctus. Etiam a purus massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam non felis ultrices, dignissim metus mattis, interdum urna.

	Vivamus at ipsum consectetur, pellentesque lectus vitae, vulputate leo. Cras tincidunt suscipit vulputate. Aenean pretium diam dui, efficitur porttitor lorem cursus in. Aenean convallis, mauris quis fermentum vehicula, purus libero fringilla lorem, placerat ultricies magna velit sit amet neque. Aenean tempor ut eros et volutpat. Proin ac lacus et odio volutpat aliquet. Proin at erat enim. Vivamus venenatis dictum magna, id dignissim lacus molestie non. Nullam ornare placerat metus, quis aliquam orci tincidunt at. Sed semper imperdiet arcu, eu convallis eros fringilla vel.

	Nullam eget gravida ex, et tincidunt nibh. Fusce sed turpis at tellus porta sodales. Nulla eget mattis lorem, sit amet pulvinar diam. Nulla odio justo, feugiat id odio non, convallis fermentum quam. Nullam risus ligula, rhoncus sed tortor vulputate, gravida luctus quam. Pellentesque sollicitudin in sapien sit amet dictum. Sed aliquam felis ac sapien aliquet faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla vel mollis libero. Sed lectus leo, blandit vitae quam venenatis, gravida laoreet sapien. Nullam quis dolor vel eros dictum lobortis. Donec eget sem ac dolor mattis lacinia. Phasellus accumsan condimentum magna, eu dignissim ipsum.

	Quisque consequat sagittis purus. Vivamus aliquam eros quis metus dignissim, eu blandit massa dictum. Curabitur vel fringilla elit. Fusce orci augue, vehicula id finibus eu, viverra sed nulla. Quisque pretium augue augue, eget mattis orci tincidunt tincidunt. Donec fermentum odio placerat vestibulum fermentum. Vivamus eu posuere sapien.

	Mauris ac elit vitae massa dignissim posuere. Sed blandit nibh ut elementum ullamcorper. Nunc facilisis elit eget lorem bibendum, eu fermentum neque ultrices. Etiam vestibulum gravida sollicitudin. Nullam velit quam, luctus vel suscipit id, ullamcorper sit amet ipsum. Donec a elit ac lorem porttitor gravida. Sed non dui sed lacus vulputate varius. Nullam in tincidunt odio, ac pharetra mauris. Integer ac volutpat quam. Mauris fermentum facilisis porttitor. Nunc ornare vel erat volutpat consectetur. Phasellus ut lacinia ante. Vestibulum massa orci, tincidunt sit amet urna in, maximus mollis ligula.',
	"post_images"   => array(
		"$dummy_image_url/ps/psf10.jpg",
		"$dummy_image_url/ps/psf102.jpg"
	),
	"post_category" => array( 'Land' ),
	"post_tags" => array( 'Real Estate' ),
	"video" => '',
	"timing" => 'Viewing Sunday 10 am to 9 pm',
	"phone" => '(222) 777-1111',
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"twitter" => 'http://example.com/',
	"facebook" => 'http://example.com/',
	"price" => '800',
	"property_status" => 'For Rent',
	'property_furnishing' => '',
	'property_type' => 'Land',
	'property_bedrooms' => '',
	'property_bathrooms' => '',
	'property_area' => '250000',
	'property_features' => '',
	"post_dummy" => '1'
);

function geodir_extra_custom_fields_property_rent( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// timing
	$fields[] = array(
		  'post_type' => $post_type,
	      'data_type' => 'VARCHAR',
	      'field_type' => 'text',
	      'admin_title' => __('Time', 'geodirectory'),
	      'frontend_desc' => __('Enter Business or Listing Timing Information.<br/>eg. : 10.00 am to 6 pm every day', 'geodirectory'),
	      'frontend_title' => __('Time', 'geodirectory'),
	      'htmlvar_name' => 'timing',
	      'default_value' => '',
	      'is_active' => '1',
	      'option_values' => '',
	      'is_default' => '0',
	      'show_in' =>  '[detail],[mapbubble]',
	      'show_on_pkg' => $package,
	      'clabels' => __('Time', 'geodirectory')
	);

	// price
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'FLOAT',
		'decimal_point'			=> '2',
		'admin_title'			=> 'Price',
		'frontend_title'		=> 'Price',
		'frontend_desc'			=> 'Enter the price in $ (no currency symbol)',
		'htmlvar_name'			=> 'price',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'show_in'				=> '[detail],[listing]',
		'is_required'			=> false,
		'validation_pattern'	=> addslashes_gpc( '\d+(\.\d{2})?' ), // add slashes required
		'validation_msg'		=> 'Please enter number and decimal only ie: 100.50',
		'required_msg'			=> '',
		'field_icon'			=> 'fas fa-dollar-sign',
		'css_class'				=> '',
		'cat_sort'				=> true,
		'cat_filter'			=> true,
		'extra'					=> array(
			'is_price'                  => 1,
			'thousand_separator'        => 'comma',
			'decimal_separator'         => 'period',
			'decimal_display'           => 'if',
			'currency_symbol'           => '$',
			'currency_symbol_placement' => 'left'
		),
		'show_on_pkg' 			=> $package,
		'clabels' 				=> 'Price'
    );

	// property status
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'select',
		'field_type_key' => 'select',
		'is_active' => 1,
		'for_admin_use' => 0,
		'is_default' => 0,
		'admin_title' => __('Property Status', 'geodirectory'),
		'frontend_desc' => __('Enter the status of the property.', 'geodirectory'),
		'frontend_title' => __('Property Status', 'geodirectory'),
		'htmlvar_name' => 'property_status',
		'default_value' => '',
		'is_required' => '1',
		'required_msg' => '',
		'show_in'   => '[detail],[listing]',
		'show_on_pkg' => $package,
		'option_values' => 'Select Status/,For Rent,Let,Under Offer',
		'field_icon' => 'fas fa-home',
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_on_pkg' => $package,
		'clabels' => 'Property Status'
	);

	// property furnishing
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'select',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Furnishing', 'geodirectory'),
		'frontend_title'      => __('Furnishing', 'geodirectory'),
		'frontend_desc'       => __('Enter the furnishing status of the property.', 'geodirectory'),
		'htmlvar_name'        => 'property_furnishing',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => true,
		'option_values'       => 'Select Status/,Unfurnished,Furnished,Partially furnished,Optional',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-th-large',
		'css_class'           => '',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Furnishing'
	);

	// property type
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'select',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Property Type', 'geodirectory'),
		'frontend_title'      => __('Property Type', 'geodirectory'),
		'frontend_desc'       => __('Select the property type.', 'geodirectory'),
		'htmlvar_name'        => 'property_type',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => true,
		'option_values'       => 'Select Type/,Detached house,Semi-detached house,Apartment,Bungalow,Semi-detached bungalow,Chalet,Town House,End-terrace house,Terrace house,Cottage,Hotel,Land',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-home',
		'css_class'           => '',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 		      => 'Property Type'
	);

	// property bedrooms
	$fields[] = array(
		'post_type' 		  => $post_type,
		'field_type'          => 'select',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Property Bedrooms', 'geodirectory'),
		'frontend_title'      => __('Bedrooms', 'geodirectory'),
		'frontend_desc'       => __('Select the number of bedrooms', 'geodirectory'),
		'htmlvar_name'        => 'property_bedrooms',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => true,
		'option_values'       => 'Select Bedrooms/,1,2,3,4,5,6,7,8,9,10',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-bed',
		'css_class'           => '',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Property Bedrooms'
	);

	// property bathrooms
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'select',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Property Bathrooms', 'geodirectory'),
		'frontend_title'      => __('Bathrooms', 'geodirectory'),
		'frontend_desc'       => __('Select the number of bathrooms', 'geodirectory'),
		'htmlvar_name'        => 'property_bathrooms',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => true,
		'option_values'       => 'Select Bathrooms/,1,2,3,4,5,6,7,8,9,10',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-bold',
		'css_class'           => '',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Property Bathrooms'
	);

	// property area
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'text',
		'data_type'           => 'INT',
		'admin_title'         => __('Property Area', 'geodirectory'),
		'frontend_title'      => __('Area (Sq Ft)', 'geodirectory'),
		'frontend_desc'       => __('Enter the Sq Ft value for the property', 'geodirectory'),
		'htmlvar_name'        => 'property_area',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => false,
		'validation_pattern'  => addslashes_gpc('\d+(\.\d{2})?'), // add slashes required
		'validation_msg'      => 'Please enter the property area in numbers only: 1500',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-chart-area',
		'css_class'           => '',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Property Area'
	);

	// property features
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'multiselect',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Property Features', 'geodirectory'),
		'frontend_title'      => __('Features', 'geodirectory'),
		'frontend_desc'       => __('Select the property features.', 'geodirectory'),
		'htmlvar_name'        => 'property_features',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => false,
		'option_values'       => 'Gas Central Heating,Oil Central Heating,Double Glazing,Triple Glazing,Front Garden,Garage,Private driveway,Off Road Parking,Fireplace',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-plus-square',
		'css_class'           => 'gd-comma-list',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Property Features'
	);

	return $fields;
}
