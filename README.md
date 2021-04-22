Symfony Standard Edition
========================

Comands
=======
CREATE PROJECT
$ composer create-project symfony/framework-standard-edition symfony/ "3.0.7"

CREATE BUNDLE
$ php bin/console generate:bundle --namespace=BackendBundle --format=yml

MAPPING EXISTING DATABASE
$ php bin/console doctrine:mapping:import BackendBundle yml
$ php bin/console doctrine:generate:entities BackendBundle


MAPPING EXISTING DATABASE TABLE
$ php bin/console doctrine:mapping:import BackendBundle yml --filter="Comments"















