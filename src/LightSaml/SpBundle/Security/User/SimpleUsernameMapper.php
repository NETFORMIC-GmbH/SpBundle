<?php

namespace LightSaml\SpBundle\Security\User;

use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response;

class SimpleUsernameMapper implements UsernameMapperInterface
{
    const NAME_ID = '@name_id@';

    /** @var string[] */
    private $attributes;

    /**
     * @param string[] $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param Response $response
     *
     * @return string|null
     */
    public function getUsername(Response $response)
    {
        foreach ($response->getAllAssertions() as $assertion) {
            $username = $this->getUsernameFromAssertion($assertion);
            if ($username) {
                return $username;
            }
        }

        return null;
    }

    /**
     * @param Assertion $assertion
     *
     * @return null|string
     */
    private function getUsernameFromAssertion(Assertion $assertion)
    {
        foreach ($this->attributes as $attributeName) {
            if (self::NAME_ID == $attributeName) {
                if ($assertion->getSubject() &&
                    $assertion->getSubject()->getNameID() &&
                    $assertion->getSubject()->getNameID()->getValue()
                ) {
                    return $assertion->getSubject()->getNameID()->getValue();
                }
            } else {
                foreach ($assertion->getAllAttributeStatements() as $attributeStatement) {
                    $attribute = $attributeStatement->getFirstAttributeByName($attributeName);
                    if ($attribute && $attribute->getFirstAttributeValue()) {
                        return $attribute->getFirstAttributeValue();
                    }
                }
            }
        }

        return null;
    }
}