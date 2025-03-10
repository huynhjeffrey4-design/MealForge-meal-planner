# Preconditions:
# 1. Ensure that PHP server is running locally i.e.
# 	> php -S localhost:8080
#
# 2. For testing providers, a local instance of mysql must be running where
#    username: root
#    password: password
#    To do this you can use the docker-compose.yml via:
#    > docker-compose up
#
#  UPDATE:
#  Now both services can be run using the docker-compose.yml file:
#  > docker-compose up

php vendor/bin/codecept run Integration
