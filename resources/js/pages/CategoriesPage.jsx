import React from 'react';
import CRUDPage from '../components/CRUDPage';

export default function CategoriesPage() {
    const fields = [
        { name: 'name', label: 'Category Name', required: true },
        {
            name: 'description',
            label: 'Description',
            type: 'textarea',
            rows: 2,
            required: false,
            fullWidth: true,
        },
    ];

    return (
        <CRUDPage
            title="Categories"
            endpoint="categories"
            fields={fields}
            searchPlaceholder="Search categories..."
            createLabel="Add Category"
            csvConfig={{
                filename: 'categories.csv',
                mapRow: (item) => ({
                    'Category Name': item.name || '',
                    Description: item.description || '',
                }),
            }}
        />
    );
}