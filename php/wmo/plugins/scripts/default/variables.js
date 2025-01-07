/*start
{
    "placeholders":"Placeholders",
    "variables":"Variables"
}
end*/
/*startDescription Substitute placeholders for variables in your html. endDescription*/
$(document).ready(function() {
    // Define placeholders and variables
    const placeholders = '#placeholders#'; //'calcYears, currentYear';
    const variables = '#variables#'//'new Date().getFullYear() - 1989, new Date().getFullYear()';

    // Split placeholders and variables into arrays
    const placeholderArray = placeholders.split(',').map(p => p.trim());
    const variableArray = variables.split(',').map(v => v.trim());

    // Calculate values by evaluating the variables
    const calculatedValues = variableArray.map(v => eval(v)); // Evaluate each expression

    // Update HTML with calculated values
    placeholderArray.forEach((placeholder, index) => {
        const value = calculatedValues[index];

        // Replace content of elements with the matching ID
        $(`#${placeholder}`).text(value);
    });
});