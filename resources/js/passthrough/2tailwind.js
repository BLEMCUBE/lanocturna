import { usePassThrough } from "primevue/passthrough";
import Tailwind from "primevue/passthrough/tailwind";

//Tailwind customization
const Tailwind_PT = usePassThrough(
    Tailwind,
    {
        button: {
            root: {
              class:
                "",
            },
            label: {
              class: "flex-1 font-bold",
            },
          },
        panel: {
            title: {
                class: ['leading-none font-light text-2xl']
            }
        },
        table:{
            header:{
                class:'bg-red-600'
            }

        }
    },
    {
        merge: true,             // Used to merge PT options. The default is true.
        useMergeProps: true,    // Whether to use Vue's 'mergeProps' method to merge PT options.
    }
);

export default Tailwind_PT;
