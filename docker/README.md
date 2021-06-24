**Welcome to Spectros Docker**

    This recipe will create a small LAMP enviroment complete with an empty instance of MySQL to get started doing development.

**Install Docker**

    https://docs.docker.com/get-docker/

**Building the container**

    $ cd docker/

**Getting it running**

    To start your environment, run this command in this folder of your local terminal: 
        $ docker-compose up

**Debugging**

Useful commands to work with your environment
    $ docker-compose ps
    $ docker-compose logs
      
**Connect to Docker MySQL**

    $ mysql -h localhost -P 8082 --protocol=tcp -u spectros -p
        
    @TODO
        We'll need to get this to work using the porkchop proper upgrade and install methods already build in rather than creating the entire DB from create_schema.sql
        -- coming soon
