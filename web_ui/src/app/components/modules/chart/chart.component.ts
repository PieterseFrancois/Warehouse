import { Component, OnDestroy, OnInit } from '@angular/core';
import { Chart } from 'chart.js/auto';
import { inArray } from 'jquery';
import { Subject, takeUntil } from 'rxjs';
import { ProductState } from 'src/app/global/state/product-state';
import { Product } from 'src/app/global/types';

@Component({
    selector: 'app-chart',
    templateUrl: './chart.component.html',
    styleUrls: ['./chart.component.scss']
})

export class ChartComponent implements OnInit, OnDestroy {

    categories: string[] = [ "Food", "Clothes", "Medicine", "Household" ];
    quantities: number[] = [ 0, 0, 0, 0 ];

    chart: Chart = undefined;

    products: Product[] = undefined;

    private destroy$: Subject<boolean> = new Subject();

    constructor( private product_state: ProductState ) { }

    ngOnInit() { 
        //validate role

        this.product_state.products$
        .pipe( takeUntil( this.destroy$ ) )
        .subscribe(
            ( products: Product[] ) => {

                if ( products === undefined ) return;

                this.products = products;

                //sum quantities
                this.sumQuantities( products );

                if ( this.chart === undefined )
                    this.createChart();
                else
                    this.chart.update();
            }
        );
    }

    ngOnDestroy(): void {
        this.destroy$.next(true);
        this.destroy$.complete();
    }

    createChart() {
        
        this.chart = new Chart( "category_quantity", {
          type: 'bar',
    
          data: {
            labels: this.categories, 
            datasets: [{
                label: "Quantity",
                data: this.quantities,
                backgroundColor: "teal",
            }]
          },

          options: {
            indexAxis: 'y',
          }
          
        });
    }
    
    sumQuantities( products: Product[] ) {

        this.quantities = [ 0, 0, 0, 0 ];

        products.forEach( ( element ) => {
            
            const result = inArray( element.category, this.categories );

            if ( result === -1 )
            {
                alert( `An error occurred whilst generating the chart. #${element.category} is not a valid category.` );
                return;
            }

            this.quantities[ result ] += element.quantity;

        });

    }

}
