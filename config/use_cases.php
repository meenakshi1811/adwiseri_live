<?php

/*
|--------------------------------------------------------------------------
| Use Cases (subscriber practice types)
|--------------------------------------------------------------------------
| One list for the public site: the header "Use Cases" mega menu, the
| mobile menu, the homepage marquee and the homepage Use Cases grid.
|
| name        Full label (menus and the Use Cases grid)
| short       Compact label for the scrolling practice strip
| definition  One-line explanation, shown on hover
| type        "practice" (by practice type) or "region" (by region & regulation)
| icon        Icon id from partials/site_icons (ignored when "code" is set)
| code        Country code badge for regional entries
*/

return [
    [
        'name' => 'General (All countries) Visa Consultants',
        'short' => 'General Visa Consultants',
        'definition' => 'A Visa/Immigration Consultant, dealing in almost all countries and across all visa categories.',
        'type' => 'practice',
        'icon' => 'globe',
    ],
    [
        'name' => 'Study Abroad (Student) Visa Consultants',
        'short' => 'Study Abroad Consultants',
        'definition' => 'A consultant helping students with university/college admissions and student visas for studying abroad.',
        'type' => 'practice',
        'icon' => 'cap',
    ],
    [
        'name' => 'Business / Work Visa Specialists',
        'short' => 'Business / Work Visa Specialists',
        'definition' => 'A specialist in business, work-permit and employer-sponsored visas for professionals and skilled workers.',
        'type' => 'practice',
        'icon' => 'briefcase',
    ],
    [
        'name' => 'PR / Immigration Advisories',
        'short' => 'PR / Immigration Advisories',
        'definition' => 'An advisory focused on permanent residency (PR), settlement and long-term immigration pathways.',
        'type' => 'practice',
        'icon' => 'home',
    ],
    [
        'name' => 'USA - Immigration Attorneys',
        'short' => 'USA Immigration Attorneys',
        'definition' => 'A licensed US immigration attorney handling US visas, green cards and related legal matters.',
        'type' => 'region',
        'code' => 'US',
    ],
    [
        'name' => 'UK - OISC (IAA) Advisors',
        'short' => 'UK OISC (IAA) Advisors',
        'definition' => 'An immigration adviser regulated by the OISC, now the Immigration Advice Authority (IAA), advising on UK visas and immigration.',
        'type' => 'region',
        'code' => 'UK',
    ],
    [
        'name' => 'Australia - MARA Advisors',
        'short' => 'Australia MARA Advisors',
        'definition' => 'A registered migration agent (MARA) advising on Australian visas and migration.',
        'type' => 'region',
        'code' => 'AU',
    ],
    [
        'name' => 'Canada - ICCRC Advisors',
        'short' => 'Canada ICCRC Advisors',
        'definition' => 'A licensed Canadian immigration consultant (RCIC), regulated by the ICCRC, now the CICC, advising on Canadian visas and immigration.',
        'type' => 'region',
        'code' => 'CA',
    ],
    [
        'name' => 'CBI - Citizenship By Investment Consultants',
        'short' => 'Citizenship By Investment',
        'definition' => 'A consultant helping investors obtain second citizenship or residency through government investment programs.',
        'type' => 'practice',
        'icon' => 'landmark',
    ],
    [
        'name' => 'Travel Agents (Visit Visa Specialists)',
        'short' => 'Visit Visa / Travel Agents',
        'definition' => 'A travel agency that also arranges visit/tourist visas alongside travel bookings.',
        'type' => 'practice',
        'icon' => 'plane',
    ],
    [
        'name' => 'Immigration Law Firms',
        'short' => 'Immigration Law Firms',
        'definition' => 'A law firm whose lawyers provide legal advice and representation on immigration and visa matters.',
        'type' => 'practice',
        'icon' => 'scale',
    ],
];
