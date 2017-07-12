<?php
/**
 * Terms Functions
 *
 * All functions related to terms(categories/tags).
 *
 * @package GeoDirectory
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function geodir_get_cat_icon( $term_id, $full_path = false, $default = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );
    
    $cat_icon = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';
    
    if ( !$cat_icon && $default ) {
        $cat_icon = geodir_get_option( 'geodir_default_marker_icon' );
    }
    
    if ( $cat_icon && $full_path && strpos( $cat_icon, 'http://' ) !== 0 && strpos( $cat_icon, 'https://' ) !== 0 ) {
        $upload_dir = wp_upload_dir();
        $cat_icon = $upload_dir['baseurl'] . '/' . $cat_icon;
    }
    
    return apply_filters( 'geodir_get_cat_icon', $cat_icon, $full_path, $default );
}

function geodir_get_cat_image( $term_id, $full_path = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );
    
    $cat_image = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';
        
    if ( $cat_image && $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
        $upload_dir = wp_upload_dir();
        $cat_image = $upload_dir['baseurl'] . '/' . $cat_image;
    }
    
    return apply_filters( 'geodir_get_cat_image', $cat_image, $full_path );
}

function geodir_get_cat_top_description( $term_id, $full_path = false ) {
    $term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );
    
    $cat_image = '';
    if ( is_array( $term_meta ) && !empty( $term_meta['src'] ) ) {
        $cat_image = $term_meta['src'];
        
        if ( $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
            $upload_dir = wp_upload_dir();
            $cat_image = $upload_dir['baseurl'] . '/' . $cat_image;
        }
    }
    
    return apply_filters( 'geodir_get_cat_top_description', $cat_image, $full_path );
}

function geodir_get_cat_schemas() {
    $schemas = array(
        '' => __( 'Default (LocalBusiness)', 'geodirectory' ),
        'AccountingService' => 'AccountingService',
        'Attorney' => 'Attorney',
        'AutoBodyShop' => 'AutoBodyShop',
        'AutoDealer' => 'AutoDealer',
        'AutoPartsStore' => 'AutoPartsStore',
        'AutoRental' => 'AutoRental',
        'AutoRepair' => 'AutoRepair',
        'AutoWash' => 'AutoWash',
        'Bakery' => 'Bakery',
        'BarOrPub' => 'BarOrPub',
        'BeautySalon' => 'BeautySalon',
        'BedAndBreakfast' => 'BedAndBreakfast',
        'BikeStore' => 'BikeStore',
        'BookStore' => 'BookStore',
        'CafeOrCoffeeShop' => 'CafeOrCoffeeShop',
        'Campground' => 'Campground',
        'ChildCare' => 'ChildCare',
        'ClothingStore' => 'ClothingStore',
        'ComputerStore' => 'ComputerStore',
        'DaySpa' => 'DaySpa',
        'Dentist' => 'Dentist',
        'DryCleaningOrLaundry' => 'DryCleaningOrLaundry',
        'Electrician' => 'Electrician',
        'ElectronicsStore' => 'ElectronicsStore',
        'EmergencyService' => 'EmergencyService',
        'EntertainmentBusiness' => 'EntertainmentBusiness',
        'Event' => 'Event',
        'EventVenue' => 'EventVenue',
        'ExerciseGym' => 'ExerciseGym',
        'FinancialService' => 'FinancialService',
        'Florist' => 'Florist',
        'FoodEstablishment' => 'FoodEstablishment',
        'FurnitureStore' => 'FurnitureStore',
        'GardenStore' => 'GardenStore',
        'GeneralContractor' => 'GeneralContractor',
        'GolfCourse' => 'GolfCourse',
        'HairSalon' => 'HairSalon',
        'HardwareStore' => 'HardwareStore',
        'HealthAndBeautyBusiness' => 'HealthAndBeautyBusiness',
        'HobbyShop' => 'HobbyShop',
        'HomeAndConstructionBusiness' => 'HomeAndConstructionBusiness',
        'HomeGoodsStore' => 'HomeGoodsStore',
        'Hospital' => 'Hospital',
        'Hostel' => 'Hostel',
        'Hotel' => 'Hotel',
        'HousePainter' => 'HousePainter',
        'HVACBusiness' => 'HVACBusiness',
        'InsuranceAgency' => 'InsuranceAgency',
        'JewelryStore' => 'JewelryStore',
        'LiquorStore' => 'LiquorStore',
        'Locksmith' => 'Locksmith',
        'LodgingBusiness' => 'LodgingBusiness',
        'MedicalClinic' => 'MedicalClinic',
        'MensClothingStore' => 'MensClothingStore',
        'MobilePhoneStore' => 'MobilePhoneStore',
        'Motel' => 'Motel',
        'MotorcycleDealer' => 'MotorcycleDealer',
        'MotorcycleRepair' => 'MotorcycleRepair',
        'MovingCompany' => 'MovingCompany',
        'MusicStore' => 'MusicStore',
        'NailSalon' => 'NailSalon',
        'NightClub' => 'NightClub',
        'Notary' => 'Notary',
        'OfficeEquipmentStore' => 'OfficeEquipmentStore',
        'Optician' => 'Optician',
        'PetStore' => 'PetStore',
        'Physician' => 'Physician',
        'Plumber' => 'Plumber',
        'ProfessionalService' => 'ProfessionalService',
        'RealEstateAgent' => 'RealEstateAgent',
        'Residence' => 'Residence',
        'Restaurant' => 'Restaurant',
        'RoofingContractor' => 'RoofingContractor',
        'RVPark' => 'RVPark',
        'School' => 'School',
        'SelfStorage' => 'SelfStorage',
        'ShoeStore' => 'ShoeStore',
        'SkiResort' => 'SkiResort',
        'SportingGoodsStore' => 'SportingGoodsStore',
        'SportsClub' => 'SportsClub',
        'Store' => 'Store',
        'TattooParlor' => 'TattooParlor',
        'Taxi' => 'Taxi',
        'TennisComplex' => 'TennisComplex',
        'TireShop' => 'TireShop',
        'TouristAttraction' => 'TouristAttraction',
        'ToyStore' => 'ToyStore',
        'TravelAgency' => 'TravelAgency',
        //'VacationRentals' => 'VacationRentals', // Not recognised by google yet
        'VeterinaryCare' => 'VeterinaryCare',
        'WholesaleStore' => 'WholesaleStore',
        'Winery' => 'Winery'
    );
    
    /*
     * Allows you to add/filter the cat schema types.
     *
     * @since 1.5.7
     */
    return apply_filters( 'geodir_cat_schemas', $schemas );
}