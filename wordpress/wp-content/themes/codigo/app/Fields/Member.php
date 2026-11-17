<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

/**
 * Team member data used outside the member's own page (project Team lists, the People grid).
 * The photo is the featured image; the "About" text is written in the editor.
 */
class Member extends Field
{
    public function fields(): array
    {
        $fields = Builder::make('member', [
            'title' => 'Team member',
            'position' => 'side',
        ]);

        $fields
            ->setLocation('post_type', '==', 'member');

        $fields
            ->addText('role', [
                'label' => 'Role',
                'instructions' => 'e.g. Founding Director',
                'required' => 1,
            ])
            ->addRelationship('key_projects', [
                'label' => 'Key projects',
                'post_type' => ['project'],
                'filters' => ['search'],
                'return_format' => 'id',
            ]);

        return $fields->build();
    }
}
