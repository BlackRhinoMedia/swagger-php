<?php

namespace Swagger\Annotations;

/**
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 *             Copyright [2013] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package
 * @category
 * @subpackage
 */
use Swagger\Annotations\Parameters;
use Swagger\Annotations\ResponseMessages;
use Swagger\Logger;

/**
 * The Operation is what is shown as a bar-like container in the interactive UI.
 * @package
 * @category
 * @subpackage
 *
 * @Annotation
 */
class Operation extends AbstractAnnotation
{
    /**
     * This is the HTTP method required to invoke this operation--the allowable values are GET, POST, PUT, DELETE.
     * @var string
     */
    public $method;

    /**
     * This is a short summary of what the operation does.
     * @var string (max 60 characters)
     */
    public $summary;

    /**
     * This is a required field provided by the server for the convenience of the UI and client code generator, and is used to provide a shebang in the swagger-ui.
     * @var string
     */
    public $nickname;

    /**
     * This is what is returned from the method--in short, it's either void, a simple-type, a complex or a container return value.
     * @var string
     */
    public $type;

    /**
     * These are the inputs to the operation.
     * @var array|Parameter
     */
    public $parameters = array();

    /**
     * An array describing the responseMessage cases returned by the operation.
     * @var array|ResponseMessage
     */
    public $responseMessages = array();

    /**
     * A longer text field to explain the behavior of the operation.
     * @var string
     */
    public $notes;

    /**
     * @param array $values
     */
    public function __construct($values)
    {
        parent::__construct($values);
        $this->notes = $this->removePreamble($this->notes);
    }

    public function setNestedAnnotations($annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Parameter) {
                $this->parameters[] = $annotation;
            } elseif ($annotation instanceof Parameters) {
                foreach ($annotation->parameters as $parameter) {
                    $this->parameters[] = $parameter;
                }
            } elseif ($annotation instanceof ResponseMessage) {
                $this->responseMessages[] = $annotation;
            } elseif ($annotation instanceof ResponseMessages) {
                foreach ($annotation->responseMessages as $responseMessage) {
                    $this->responseMessages[] = $responseMessage;
                }
            } else {
                Logger::notice('Unexpected '.get_class($annotation).' in a '.get_class($this).' in '.AbstractAnnotation::$context);
            }
        }
    }

    public function validate()
    {
        if (empty($this->nickname)) {
            Logger::notice('The optional field "nickname" is required for the swagger-ui client for an "'.get_class($this).'" in '.AbstractAnnotation::$context);
        }
        foreach ($this->parameters as $parameter) {
            if ($parameter->validate() == false) {
                return false;
            }
        }
        return true;
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        if (count($this->responseMessages) === 0) {
            unset($data['responseMessages']);
        }
        if (count($this->parameters) === 0) {
            unset($data['parameters']);
        }
        return $data;
    }
}