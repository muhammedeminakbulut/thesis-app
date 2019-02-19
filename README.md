# Thesis app
In this readme we will briefly describe how to recreate the actions taken to calculate the data for the thesis. 
This data is also available in another github repo. https://github.com/muhammedeminakbulut/thesis-data

## requirements
See composer.json

## installation
`composer install`

## configuration
in `config/services.yml` there are a lot of self explaining paths defined to store data to.

## collecting data
to collect data there needs to be a github api v3 response in JSON which can be processed by the command `php bin/console app:create-jobs`.
This command kicks in all the repo's into a beanstalkd queue to process.
To process you can either run every command manually or use a supervisord setup to run multiple commands continuously.
By running `php bin/console app:measure` one repo is taken from the queue to calculate all the metrics for this thesis.
It is stored as csv into the `data/mesaurement-data/` directory. After all the measurement is finished we want this data in one csv file.
For this we have `php bin/console app:merge` which merges the data into one csv.
After we have merged this data we want to rate this data by the star rating system as mentioned in the thesis.
By calling `php bin/console app:rate` a csv file is created with the rating.
After this file is created `php bin/console app:correlate` can be run.

This lasts command gives the final result of a correlation. The sniffer errors column in all these metrics will be the sniffer ruleset you have configured in the `services.yml`

The above steps are developed in ways it can be used for one ruleset in PHPCS. 

### test coverage data
for test coverage data we have developed an alternative path because we developed this after the first initial setup.

It looks similar to the steps above.
`php bin/console app:create-test`
`php bin/console app:measure-test`
`php bin/console app:merge-test`
`php bin/console app:rate-test`

are the steps like the ones above. After which the following steps are required for correlation.

`php bin/console app:merge-metric-test master-file-path slave-file-path` because the data obtained from the test measurements can be less or the other way around.
We asume the master file is the test rate csv which contains less rows. And the slave file is the file with the most rows. 
This command zips the two with overlapping repositories and tags into one.

After this is finished we can again correlate the data with `php bin/console app:correlate-test`

## Classification
to be able to classify and get the numbers behind the star ratings or as others call it the percentiles we have the command
`php bin/console app:classify` which can perform an output of the classification with the obtained data for reference purposes.


## questions?
Open an issue
