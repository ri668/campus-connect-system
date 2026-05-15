<?php

define('CAMPUS_BACKGROUND_PATH', 'backgrounds');

define('CAMPUS_IMAGE_MAP', [
    'UNIVERSITY OF BAGUIO'             => 'university_of_baguio_logo.png',
    'SAINT LOUIS UNIVERSITY'           => 'saint_louis_university.png',
    'UNIVERSITY OF PHILIPPINES'        => 'university_of_philippines.png',
    'BAGUIO CENTRAL UNIVERSITY'        => 'baguio_central_university.png',
    'PINES CITY COLLEGES'              => 'pines_city_colleges.png',
    'PINES CITY NATIONAL HIGH SCHOOL'  => 'pines_city_national_high_school.avif',
    'GUISAD VALLEY'                    => 'guisad_valley.jfif',
    'BENGUET NATIONAL HIGH SCHOOL'     => 'benguet_national_high_school.png',
    'EASTER COLLEGES'                  => 'acatech_aviation_college.png',
    'ACATECH AVIATION COLLEGE'         => 'acatech_aviation_college.png',
    'UNIVERSITY OF CORDILLERAS'        => 'university_of_cordilleras.png',
    'QUEZON HILL NATIONAL HIGH SCHOOL' => 'quezon_hill_national_high_school.jfif',
    'BENGUET STATE UNIVERSITY'         => 'benguet_state_university.png',
    'OTHER CAMPUS'                     => '',
]);

function getCampusIcon($campus) {
    $file = CAMPUS_IMAGE_MAP[$campus] ?? '';
    return $file ? CAMPUS_BACKGROUND_PATH . '/' . $file : '';
}

function getCampusBackground($campus) {
    $file = getCampusIcon($campus);
    return $file ?: 'background.jpg';
}

function renderCampusLogo($campus, $size = 40) {
    $src = getCampusIcon($campus);
    if (!$src) {
        return flameIcon($size);
    }
    return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($campus) . ' logo" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:contain;border-radius:50%;background:#fff;padding:2px;">';
}
