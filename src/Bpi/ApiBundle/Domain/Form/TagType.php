<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 28.07.2015
 * Time: 11:23
 */

namespace Bpi\ApiBundle\Domain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tag');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bpi\ApiBundle\Domain\Entity\Tag',
        ));
    }

    public function getName()
    {
        return 'tag';
    }
}
