# Maboo Maker Bundle

Boilerplate code generator for projects with layered architecture

## Installation
To install this package, use Composer:
```shell
composer require bornfight/maboo-maker-bundle --dev
```
If you are using [Symfony Flex](https://symfony.com/doc/current/setup.html#symfony-flex), this will install and enable the Maboo Maker bundle automatically.  
If not, you should add this manually to your `config/bundles.php` file:
```php
return [
    // ...
    Bornfight\MabooMakerBundle\BornfightMabooMakerBundle::class => ['dev' => true, 'test' => true],
];
```

# Usage
There are multiple commands available which can work independently, but whenever possible, you can make your life easier just by running:
```bash
bin/console make:maboo-scaffold
```

**List of currently supported sub-commands:**
```bash
make:maboo-module        # Creates a bounded context (module) folder (if it does not exist yet)                       
make:maboo-entity        # Creates or updates a Doctrine entity class                                               
make:maboo-domain-model  # Creates or updates a domain model class                                                  
make:maboo-write-models  # Creates or updates write models for a model class                                        
make:maboo-entity-mapper # Creates or updates mapper for a model class                                              
make:maboo-repository    # Creates or updates a repository interface and concrete implementation                    
make:maboo-validator     # Creates or updates a validator and specification                                         
make:maboo-manager       # Creates or updates a resource manager                                                    
make:maboo-resolver      # Creates or updates a resolver                                                            
make:maboo-mutation      # Creates or updates a mutation class                                                      
make:maboo-gql-schema    # Creates or updates GraphQL types and schema 
```
`make:maboo-scaffold` will start the interactive wizard and ask you which of the components you need and then internally execute all selected commands one by one.
