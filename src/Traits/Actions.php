<?php

namespace Slakbal\Gotowebinar\Traits;

use Slakbal\Gotowebinar\Contract\GotoClientContract;

trait Actions
{
    use RequestHelpers;

    protected $connection;

    public function connection(string $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function getGotoClient()
    {
        return app(GotoClientContract::class)
            ->setConnection($this->connection);
    }

    public function dump(array $data = [])
    {
        //set all the properties of the parent
        $this->setDataByMethod($data);

        //create the payload - the client will extract the payload
        print_r('Payload:');
        dump($this->toArray());

        //Show the exclusions
        print_r('Payload Exclusions:');
        dump($this->getPayloadExclusions());

        //create the query parameters - the client will extract the query parameters
        print_r('Query Parameters:');
        dump($this->queryParameters);

        //build the path for the resource - the client can do this
        print_r('Full Path:');
        dump($this->getResourceFullPath());

        //show keys for path replacement
        print_r('Path Keys:');
        dump($this->pathKeys);
    }

    public function create(array $data = [])
    {
        //set all the properties of the parent
        $this->setDataByMethod($data);

        //validate if the required fields are set
        $this->validate($this->requiredFields());

        return $this->getGotoClient()->setPath($this->getResourceFullPath())
                                 ->setPathKeys($this->pathKeys)
                                 ->setParameters($this->queryParameters)
                                 ->setPayload($this->getPayload())
                                 ->create();
    }

    public function update(array $data = [])
    {
        //set all the properties of the parent
        $this->setDataByMethod($data);

        return $this->getGotoClient()->setPath($this->getResourceFullPath())
                                 ->setPathKeys($this->pathKeys)
                                 ->setParameters($this->queryParameters)
                                 ->setPayload($this->getPayload())
                                 ->update();
    }

    public function get()
    {
        return $this->getGotoClient()->setPath($this->getResourceFullPath())
                                 ->setPathKeys($this->pathKeys)
                                 ->setParameters($this->queryParameters)
                                 ->setPayload($this->getPayload())
                                 ->get();
    }

    public function delete()
    {
        return $this->getGotoClient()->setPath($this->getResourceFullPath())
                                 ->setPathKeys($this->pathKeys)
                                 ->setParameters($this->queryParameters)
                                 ->setPayload($this->getPayload())
                                 ->delete();
    }

    public function status()
    {
        return $this->getGotoClient()->status();
    }

    public function authenticate()
    {
        return $this->getGotoClient()->authenticate();
    }

    public function flushAuthentication()
    {
        return $this->getGotoClient()->flushAuthentication();
    }
}
