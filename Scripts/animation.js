//options for the observer
const options = {
  root: null,
  rootMargin: "0px",
  threshold: 0.1,
};

//create the observer
const Shows = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("show");
    } else {
      entry.target.classList.remove("show");
    }
  });
}, options);

const SlidesLeft = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("left");
      // Remove the animation class after it finishes
      entry.target.addEventListener("animationend", () => {
        entry.target.classList.remove("left");
      });
    }
  });
}, options);

//elements to be animated
const HiddenElements = document.querySelectorAll(".hidden");
const SlideElements = document.querySelectorAll(".slides");

//observe each element
HiddenElements.forEach((Element) => Shows.observe(Element));
SlideElements.forEach((Element) => SlidesLeft.observe(Element));





