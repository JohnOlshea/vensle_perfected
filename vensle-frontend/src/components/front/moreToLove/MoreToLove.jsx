import { Fragment, useState } from 'react'
import { Dialog, RadioGroup, Transition } from '@headlessui/react'
import { XMarkIcon } from '@heroicons/react/24/outline'
import { StarIcon } from '@heroicons/react/20/solid'



import img4 from "assets/img/front/categories/img4.JPG";
import img5 from "assets/img/front/categories/img5.JPG";


function classNames(...classes) {
  return classes.filter(Boolean).join(' ')
}

const MoreToLove = () => {
    const [selectedProduct, setSelectedProduct] = useState(null);


    const [open, setOpen] = useState(false)


    const handleModal = (e, product) => {
	e.preventDefault();
	setSelectedProduct(product);
	setOpen(true);
    }



	return (
<>
      <div className="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-12 lg:max-w-7xl lg:px-8">
        <h2 className="text-2xl font-normal tracking-tight text-gray-900 uppercase">More to Love</h2>

        <div className="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-5 xl:gap-x-8">
	  {[0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].map((product) => (
            <div onClick={(e) => handleModal(e, product)} key={product} style={{"background": "#eee"}} className="group relative rounded-md">
<div className="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-40">
		  {/*<div className="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75">*/}
                <img
                  src={img4}
                  alt="product"
                  className="h-full w-full object-cover object-center lg:h-full lg:w-full"
                />
              </div>
	      <div className="p-2">

		      <h2 className="text-lg font-medium text-gray-900" style={{"fontWeight": "500", "fontSize":"1rem"}}>Dell Computer</h2>

		      <div className="mt-1 flex items-center">
			{[0, 1, 2, 3, 4].map((rating) => (
			  <StarIcon
			    key={rating}
			    className='text-orange-900 h-3 w-3 mr-1 flex-shrink-0'
			    aria-hidden="true"
			  />
			))}
		      </div>


		      <div className="mt-2 flex justify-between">
			<div>
			  <h3 className="text-sm text-gray-700">
			    <a href={product.href}>
			      <span aria-hidden="true" className="absolute inset-0" />
			      124.50
			    </a>
			  </h3>
			</div>
			<p className="text-xs flex items-center text-black-200 font-medium text-gray-700" style={{"color":"#aaa"}}>
		  	
<svg class="h-3 w-3 mr-1 text-gray-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>

		  		London
		  	</p>
		      </div>
	      </div>

            </div>
	  ))}
        </div>
      </div>








    <Transition.Root show={open} as={Fragment}>
      <Dialog as="div" className="relative z-10" onClose={setOpen}>
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 hidden bg-gray-500 bg-opacity-75 transition-opacity md:block" />
        </Transition.Child>

        <div className="fixed inset-0 z-10 w-screen overflow-y-auto">
          <div className="flex min-h-full items-stretch justify-center text-center md:items-center md:px-2 lg:px-4">
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 translate-y-4 md:translate-y-0 md:scale-95"
              enterTo="opacity-100 translate-y-0 md:scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 translate-y-0 md:scale-100"
              leaveTo="opacity-0 translate-y-4 md:translate-y-0 md:scale-95"
            >
              <Dialog.Panel className="flex w-full transform text-left text-base transition md:my-8 md:max-w-6xl md:px-4 lg:max-w-7xl">
                <div className="relative flex w-full items-center overflow-hidden bg-white px-4 pb-8 pt-14 shadow-2xl sm:px-6 sm:pt-8 md:p-6 lg:p-8">
                  <button
                    type="button"
                    className="absolute right-4 top-4 text-gray-400 hover:text-gray-500 sm:right-6 sm:top-8 md:right-6 md:top-6 lg:right-8 lg:top-8"
                    onClick={() => setOpen(false)}
                  >
                    <span className="sr-only">Close</span>
                    <XMarkIcon className="h-6 w-6" aria-hidden="true" />
                  </button>





    <div className="bg-white">
      <div className="mx-auto max-w-7xl">
        <div className="mx-auto rounded-3xl ring-1 ring-gray-200 lg:mx-0 lg:flex lg:max-w-none">



                <div class="w-full px-4">
                    <div class="sticky top-0 z-50 overflow-hidden ">
                        <div class="relative mb-6 lg:mb-10 lg:h-[28rem]">
                            <img style={{"width":"200rem", "padding": "0 8rem"}} src="https://i.postimg.cc/PqYpFTfy/pexels-melvin-buezo-2529148.jpg" alt=" /"
                                class="object-cover w-full lg:h-full " />
                        </div>
                        <div class="flex-wrap hidden md:flex ">
                            <div class="w-1/2 p-2 sm:w-1/4">
                                <a href="#"
                                    class="block border border-blue-300 dark:border-transparent dark:hover:border-blue-300 hover:border-blue-300">
                                    <img src="https://i.postimg.cc/PqYpFTfy/pexels-melvin-buezo-2529148.jpg" alt=" /"
                                        class="object-cover w-full lg:h-20"/>
                                </a>
                            </div>
                            <div class="w-1/2 p-2 sm:w-1/4">
                                <a href="#"
                                    class="block border border-transparent dark:border-transparent dark:hover:border-blue-300 hover:border-blue-300">
                                    <img src="https://i.postimg.cc/PqYpFTfy/pexels-melvin-buezo-2529148.jpg" alt=" /"
                                        class="object-cover w-full lg:h-20"/>
                                </a>
                            </div>
                            <div class="w-1/2 p-2 sm:w-1/4">
                                <a href="#"
                                    class="block border border-transparent dark:border-transparent dark:hover:border-blue-300 hover:border-blue-300">
                                    <img src="https://i.postimg.cc/PqYpFTfy/pexels-melvin-buezo-2529148.jpg" alt=" /"
                                        class="object-cover w-full lg:h-20"/>
                                </a>
                            </div>
                            <div class="w-1/2 p-2 sm:w-1/4">
                                <a href="#"
                                    class="block border border-transparent dark:border-transparent dark:hover:border-blue-300 hover:border-blue-300">
                                    <img src="https://i.postimg.cc/PqYpFTfy/pexels-melvin-buezo-2529148.jpg" alt=" /"
                                        class="object-cover w-full lg:h-20"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>




          <div className="-mt-2 p-2 lg:mt-0 lg:w-full lg:max-w-md lg:flex-shrink-0">
            <div className="rounded-2xl bg-gray-50 py-5 ring-1 ring-inset ring-gray-900/5 lg:flex lg:flex-col lg:justify-center lg:pb-8 lg:pt:6">
              <div className="mx-auto px-8">
		<h3 className="text-2xl mb-5 font-bold tracking-tight text-gray-900">
	    {selectedProduct}	Women New Syle Dresses Fashion Ladies Printed V-next Summer Long to hidden
	  	</h3>
<div className="flex items-center">
		      <div className="flex items-center">
			{[0, 1, 2, 3, 4].map((rating) => (
			  <StarIcon
			    key={rating}
			    className='text-orange-900 h-3 w-3 mr-1 flex-shrink-0'
			    aria-hidden="true"
			  />
			))}
		      </div>

                <p className="text-sm leading-5 text-gray-600">
	  		<span className="text-gray-400">4.0</span> (16 Feedbacks)
                </p>
</div>
                <h4 className="text-xl mt-3 mb-5 text-gray-600">$124.80</h4>
                <h4 className="text-xl font-semibold text-gray-600">Product Details</h4>


            <p className="mt-3 text-base leading-7 text-gray-600">
              Lorem ipsum dolor sit amet consect etur adipisicing elit. Itaque amet indis perferendis blanditiis
              repellendus etur quidem dwelling.
            </p>

			<p className="text-sm flex items-center text-black-200 font-medium text-gray-700 mt-3">
		  	
<svg class="h-3 w-3 mr-2 text-gray-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>

		  		London
		  	</p>
			<p className="text-sm flex items-center text-black-200 font-medium text-gray-700 mt-3">
		  	
<svg class="h-3 w-3 mr-2 text-gray-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
</svg>

		  		Posted 11 hours ago
		  	</p>

<div className="flex items-center">
                <a
                  href="#"
                  className="mt-10 block w-full rounded-md bg-indigo-600 px-3 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                  Get access
                </a>


                <a
                  href="#"
                  className="mt-10 ml-3 block rounded-md bg-indigo-600 px-3 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                  call
                </a>
                <a
                  href="#"
                  className="mt-10 ml-3 block rounded-md bg-indigo-600 px-3 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                  msg
                </a>
</div>	  
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition.Root>










</>
	)
}

export default MoreToLove;