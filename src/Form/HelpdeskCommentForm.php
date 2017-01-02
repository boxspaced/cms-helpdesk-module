<?php
namespace Helpdesk\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;

class HelpdeskCommentForm extends Form
{

    /**
     * @var string
     */
    protected $attachmentUploadDirectory;

    /**
     * @param string $attachmentUploadDirectory
     */
    public function __construct($attachmentUploadDirectory)
    {
        parent::__construct('helpdesk-comment');
        $this->attachmentUploadDirectory = $attachmentUploadDirectory;

        $this->setAttribute('method', 'post');
        $this->setAttribute('accept-charset', 'UTF-8');

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * @return void
     */
    protected function addElements()
    {
        $element = new Element\Csrf('token');
        $element->setCsrfValidatorOptions([
            'timeout' => 900,
        ]);
        $this->add($element);

        $element = new Element\Textarea('comment');
        $element->setLabel('Comment');
        $element->setAttribute('required', true);
        $element->setAttributes(array(
            'rows' => 8,
            'cols' => 90,
        ));
        $this->add($element);

        $element = new Element\File('attachment');
        $element->setLabel('Attach an image');
        $element->setOption('description', 'Attach an image e.g. a screen shot to help us see the problem');
        $this->add($element);

        $element = new Element\Submit('resolve');
        $element->setValue('Resolve');
        $this->add($element);

        $element = new Element\Submit('save');
        $element->setValue('Save');
        $this->add($element);
    }

    /**
     * @return HelpdeskCommentForm
     */
    protected function addInputFilter()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'comment',
            'filters' => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'attachment',
            'allow_empty' => true,
            'validators' => [
                ['name' => Validator\File\IsImage::class],
                [
                    'name' => Validator\File\Size::class,
                    'options' => [
                        'max' => '10MB',
                    ],
                ],
            ],
            'filters' => [
                [
                    'name' => Filter\File\RenameUpload::class,
                    'options' => [
                        'target' => $this->attachmentUploadDirectory . DIRECTORY_SEPARATOR . 'attachment',
                        'randomize' => true,
                        'use_upload_extension' => true,
                    ],
                ],
            ],
        ]);

        return $this->setInputFilter($inputFilter);
    }

}
