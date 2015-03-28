<?php

namespace App;

use Nette,
	Nette\Application\UI;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	protected function createComponentCreateRoomForm()
    {
        $form = new UI\Form;
        $form->addText('source', '')
				->setAttribute('class', 'appSearchInput')
				->setAttribute('placeholder', 'http://youtube.com')
				->setAttribute('ng-model', 'searchQuery')
				->setRequired('Zadejte prosím URL videa na youtube.com');
        $form->addSubmit('create', 'Go')
			->setAttribute('class', 'appSearchButt');
        $form->onSuccess[] = array($this, 'crateRoomFormSucceeded');
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function crateRoomFormSucceeded(UI\Form $form)
    {
		$values = $form->getValues();
        $this->redirect('Room:create', array('source' => $values['source']));
    }

	public function renderDefault()
	{
		$this->template->youtubePlayUrl = 'https://www.youtube.com/watch?v=';
		$this->template->randomVideoHashes = [
			'9MWbm9bQz5Y',
			'ZwzY1o_hB5Y',
			'NFfTHoJ9khs',
			'AIO2MEJCD9k',
			'q4AQDDKglEE',
			'OUi42PsV2hQ'
		];
	}

}
