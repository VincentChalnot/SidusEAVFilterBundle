Sidus/EAVFilterBundle
=====================

This bundle works on top of the [Sidus/FilterBundle](https://github.com/VincentChalnot/SidusFilterBundle) and enables
the compatibility for the [Sidus/EAVModelBundle](https://github.com/VincentChalnot/SidusEAVModelBundle).

## Installation

### Bundle setup

Require this bundle with composer:

````bash
$ composer require sidus/eav-filter-bundle "^3.0"
````

### Add the bundle to AppKernel.php

````php
<?php
/**
 * app/AppKernel.php
 */
class AppKernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            // If not already enabled:
            new Sidus\EAVModelBundle\SidusEAVModelBundle(),
            new Sidus\FilterBundle\SidusFilterBundle(),
            // This bundle:
            new Sidus\EAVFilterBundle\SidusEAVFilterBundle(),
            // ...
        ];
    }
}
````

## Configuration

Check the [Sidus/FilterBundle](https://github.com/VincentChalnot/SidusFilterBundle) for the base configuration
documentation.

Simply setup your filter configuration with the ````sidus.eav```` provider and setup the ````family```` option like
this:

````yaml
sidus_filter:
    configurations:
        my_configuration:
            provider: sidus.eav # Tells the system to use the EAV filter engine
            options:
                family: News # Required to select the proper data source
            sortable:
                - id
                - label
                - publicationDate
                - publicationStatus
                - updatedAt
            default_sort:
                publicationDate: DESC
            filters:
                label: ~
                publicationDate:
                    type: date_range
                publicationStatus:
                    type: choice
                category:
                    type: autocomplete_data # Will display an autocomplete to the related EAV family
````

## Specific features

The ````choice```` filter type will automatically load the choices from your attribute configuration.

The ````autocomplete_data```` filter type doesn't supports attributes related to multiple families for the moment.

This bundle provides a specific adapter for PagerFanta, the EAVAdapter that supports the OptimizedDataLoader from the
EAVModelBundle. Check this documentation for more information:
[EAV Query Optimization](https://github.com/VincentChalnot/SidusEAVModelBundle/blob/v1.2.x-dev/Documentation/07.2-query-optimization.md)

The depth of the loader is configurable through the ````loader_depth```` option:

### Configuration quick reference
````yaml
sidus_filter:
    configurations:
        <configuration_code>:
            provider: sidus.eav # Required
            options:
                family: <FamilyCode> # Required
                # Optional options
                loader_depth: <int> # Default 2
                query_context: <array> # If you want to inject a custom context (or part of it) statically for the query
                use_global_context: <bool> # Merge the query_context with the global context for the query
                                           # (the results always use the global context by default)
                result_context: <array> # Same as before but for the results (will inject this in Data::setCurrentContext)
````
