<?php

namespace Neoxygen\Neogen\Processor;

use Faker\Factory;
use Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Graph\Graph;

class PropertyProcessor
{

    private $faker;

    private $graph;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function process(VertEdgeProcessor $vertEdge, Graph $graph)
    {
        $this->graph = $graph;

        foreach ($vertEdge->getNodes() as $node){
            $this->addNodeProperties($node);
        }

        foreach ($vertEdge->getEdges() as $edge) {
            $this->addEdgeProperties($edge);
        }

        return $this->graph;
    }

    public function addNodeProperties(array $vertedge)
    {
        $props = [];
        foreach($vertedge['properties'] as $key => $type){
            if (is_array($type)) {
                $value = call_user_func_array(array($this->faker, $type['type']), $type['params']);
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
            } else {
                $value = $this->faker->$type;
            }
            $props[$key] = $value;
        }
        $vertedge['properties'] = $props;
        $this->graph->setNode($vertedge);
    }

    public function addEdgeProperties(array $vertedge)
    {
        if (isset($vertedge['properties'])) {

            try {
                $props = [];
                foreach($vertedge['properties'] as $key => $type){
                    if (is_array($type)) {
                        $value = call_user_func_array(array($this->faker, $type['type']), $type['params']);
                    } else {
                        $value = $this->faker->$type;
                    }
                    if ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    $props[$key] = $value;
                }
                $vertedge['properties'] = $props;
                $this->graph->setEdge($vertedge);
            } catch(\InvalidArgumentException $e) {
                $msg = $e->getMessage();
                preg_match('/((?:")(.*)(?:"))/', $msg, $output);
                if (isset($output[2])) {
                    $msg = sprintf('The faker type "%s" is not defined', $output[2]);
                }
                throw new SchemaException($msg);
            }

        }

    }
}