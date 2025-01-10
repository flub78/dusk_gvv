# Complete Guide to Table Testing with Laravel Dusk

## Table Structure
First, let's structure our table with appropriate data attributes:

```html
<table data-test="elements-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($elements as $element)
            <tr data-test="element-row" 
                data-test-id="{{ $element->id }}"
                data-test-description="{{ $element->description }}">
                <td>{{ $element->name }}</td>
                <td>{{ $element->description }}</td>
                <td>
                    <a href="/elements/{{ $element->id }}/edit" 
                       data-test-action="edit">Edit</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

## Helper Trait
Create a trait to encapsulate common table operations:

```php
trait TableTestHelpers
{
    /**
     * Get element ID by row index (1-based)
     */
    public function getElementIdByRow($browser, int $rowIndex)
    {
        return $browser
            ->elements('[data-test="element-row"]')[$rowIndex - 1]
            ->getAttribute('data-test-id');
    }

    /**
     * Get element ID by matching description
     */
    public function getElementIdByDescription($browser, string $description)
    {
        return $browser
            ->element("[data-test='element-row'][data-test-description='${description}']")
            ->getAttribute('data-test-id');
    }

    /**
     * Get element ID by partial description match
     */
    public function getElementIdByDescriptionPattern($browser, string $pattern)
    {
        return $browser
            ->script("
                return Array.from(document.querySelectorAll('[data-test=\"element-row\"]'))
                    .find(row => row.getAttribute('data-test-description')
                    .includes('${pattern}'))
                    ?.getAttribute('data-test-id');
            ")[0];
    }

    /**
     * Get element ID by matching displayed text in row
     */
    public function getElementIdByDisplayedText($browser, string $text)
    {
        return $browser
            ->script("
                return Array.from(document.querySelectorAll('[data-test=\"element-row\"]'))
                    .find(row => row.textContent.includes('${text}'))
                    ?.getAttribute('data-test-id');
            ")[0];
    }

    /**
     * Get all element IDs by matching displayed text in rows
     * Useful when multiple rows might contain the same text
     */
    public function getAllElementIdsByDisplayedText($browser, string $text)
    {
        return $browser
            ->script("
                return Array.from(document.querySelectorAll('[data-test=\"element-row\"]'))
                    .filter(row => row.textContent.includes('${text}'))
                    .map(row => row.getAttribute('data-test-id'));
            ")[0];
    }

    /**
     * Click edit button for specific row
     */
    public function clickEditForRow($browser, int $rowIndex)
    {
        $browser->elements('[data-test="element-row"]')[$rowIndex - 1]
                ->findElement('[data-test-action="edit"]')
                ->click();
    }
}
```

## Basic Test Cases

```php
class ElementTest extends DuskTestCase
{
    use TableTestHelpers;

    public function test_can_edit_third_row_element()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/elements');
            
            // Get ID from third row
            $id = $this->getElementIdByRow($browser, 3);
            
            // Click edit on third row
            $this->clickEditForRow($browser, 3);
            
            $browser->assertPathIs("/elements/{$id}/edit");
        });
    }

    public function test_can_find_element_by_exact_description()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/elements');
            
            $id = $this->getElementIdByDescription(
                $browser, 
                'Specific description'
       