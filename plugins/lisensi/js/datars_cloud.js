HTML CSS JSResult Skip Results Iframe
EDIT ON
const dataURL =
  "https://basoro.org/datars/random";
const tags = document.querySelector(".tags");
const fragment = document.createDocumentFragment();
const maxFontSizeForTag = 6;

function handleResult(result, highestValue) {
  // Set our variables
  const numberOfArticles = result.tagged_articles.length;
  const name = result.nama_instansi;
  const link = 'https://basoro.org/datars';
  let fontSize = numberOfArticles / highestValue * maxFontSizeForTag;
  fontSize = +fontSize.toFixed(2);
  
  // Make sure our font size will be at least 1em
  if (fontSize <= 1) {
    fontSize = 1;
  } else {
    fontSize = fontSize;
  }
  const fontSizeProperty = `${fontSize}em`;

  // Then, create a list element for each tag and inline the font size.
  tag = document.createElement("li");
  tag.classList.add("tag");
  tag.innerHTML = `<a class="tag__link" href="${link}" style="font-size: ${fontSizeProperty}">${name} (${numberOfArticles})</a>`;

  // Append each tag to the fragment
  fragment.appendChild(tag);
}

fetch(dataURL)
  .then(function (res) {
    return res.json();
  })
  .then(function (data) {
    // 1. Create a new array from data
    let orderedData = data.map((x) => x);
    // 2. Order it by number of articles each tag has
    orderedData.sort(function(a, b) {
      return a.tagged_articles.length - b.tagged_articles.length;
    });
    orderedData = orderedData.reverse();
    // 3. Get a value for the tag with the most articles
    const highestValue = orderedData[0].tagged_articles.length;
    // 4. Create a list item for each result from data.
    data.forEach((result) => handleResult(result, highestValue));
    // 5. Append all the tags to the tags element.
    // if (data.length > 1) {
      tags.appendChild(fragment);
    // }
  });



Resources1× 0.5× 0.25×Rerun